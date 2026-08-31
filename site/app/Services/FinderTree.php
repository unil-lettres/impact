<?php

namespace App\Services;

use App\Card;
use App\Course;
use App\Enums\CardBox;
use App\Enums\TranscriptionType;
use App\Folder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * The cards and folders of a course, loaded once and arranged as a tree.
 *
 * The finder renders a folder and all its descendants, and needs the content
 * and the recursive card count of every folder it displays. Asking for them
 * folder by folder makes each ancestor walk again the subtree its children
 * just walked, so the whole course is loaded once here and the counts are
 * computed from the children up.
 */
class FinderTree
{
    /**
     * Cards the user can list, grouped by folder id (0 at the root of the
     * course).
     */
    private Collection $cardsByFolder;

    /**
     * Folders of the course, grouped by parent id (0 at the root of the
     * course).
     */
    private Collection $foldersByParent;

    /**
     * All the cards of the course, grouped by folder id, ignoring the filters
     * and the permissions of the user.
     */
    private Collection $allCardsByFolder;

    /**
     * Memoized recursive card counts, keyed by folder id.
     */
    private array $counts = [];

    /**
     * Memoized recursive counts of all the cards, keyed by folder id.
     */
    private array $totalCounts = [];

    private function __construct(
        private readonly Collection $filters,
        private readonly string $sortColumn,
        private readonly string $sortDirection,
    ) {}

    /**
     * Load the content of the course and keep the cards matching the filters
     * that the user is allowed to list.
     *
     * See FinderItemsService::getItems() for the format of $filters and
     * $filterSearchBoxes.
     */
    public static function build(
        Course $course,
        Collection $filters,
        array $filterSearchBoxes,
        string $sortColumn = 'position',
        string $sortDirection = 'asc',
    ): static {
        $tree = new static($filters, $sortColumn, $sortDirection);

        // The whole course is loaded in two queries, the filters are applied
        // in memory so that both the filtered and the unfiltered content stay
        // available.
        $cards = Card::with('tags')->with('state')->with('folder')->with('course')
            ->where('course_id', $course->id)
            ->get();

        $folders = Folder::with('course')
            ->where('course_id', $course->id)
            ->get();

        $tree->resolveHolders($course, $cards);

        $tree->allCardsByFolder = $tree->groupByParent($cards, 'folder_id');
        $tree->foldersByParent = $tree->groupByParent($folders, 'parent_id');

        $tree->cardsByFolder = $tree->groupByParent(
            $tree->keepListableCards($course, $cards, $filterSearchBoxes),
            'folder_id',
        );

        return $tree;
    }

    /**
     * Return the cards and the folders contained inside the given folder, or
     * the ones at the root of the course when no folder is given.
     *
     * Cards are filtered, folders are not.
     */
    public function items(?Folder $folder = null): Collection
    {
        return $this
            ->cards($folder)
            ->concat($this->folders($folder))
            ->sortBy([
                [$this->sortColumn, $this->sortDirection],
                ['id', 'asc'],
            ])
            ->values();
    }

    /**
     * Return the number of cards contained in the given folder and its
     * children recursively.
     */
    public function countCardsRecursive(Folder $folder): int
    {
        return $this->counts[$folder->id] ??= $this->cards($folder)->count()
            + $this->folders($folder)->sum(
                fn ($child) => $this->countCardsRecursive($child)
            );
    }

    /**
     * Return whether the given folder contains at least one card, itself or in
     * its children recursively, ignoring the filters and the permissions of
     * the user.
     */
    public function hasCardsRecursive(Folder $folder): bool
    {
        return $this->countAllCardsRecursive($folder) > 0;
    }

    /**
     * Return whether at least one filter is selected.
     */
    public function hasFilters(): bool
    {
        return $this->filters->some(fn ($filter) => $filter->isNotEmpty());
    }

    /**
     * Return the cards the user can list inside the given folder.
     */
    private function cards(?Folder $folder): Collection
    {
        return $this->cardsByFolder->get($folder?->id ?? 0, collect());
    }

    /**
     * Return the folders inside the given folder.
     */
    private function folders(?Folder $folder): Collection
    {
        return $this->foldersByParent->get($folder?->id ?? 0, collect());
    }

    /**
     * Count the cards of the given folder and of its children recursively,
     * ignoring the filters and the permissions of the user.
     */
    private function countAllCardsRecursive(Folder $folder): int
    {
        return $this->totalCounts[$folder->id] ??= $this
            ->allCardsByFolder
            ->get($folder->id, collect())
            ->count()
            + $this->folders($folder)->sum(
                fn ($child) => $this->countAllCardsRecursive($child)
            );
    }

    /**
     * Resolve the holders of every card of the course in one pass.
     *
     * Asking a card for its holders means looking for it in every enrollment
     * of the course, so listing a course would walk them again for each of its
     * cards. The enrollments are walked once here instead, and every card is
     * given the holders it would have resolved on its own.
     */
    private function resolveHolders(Course $course, Collection $cards): void
    {
        $enrollments = $course->enrollments;
        $enrollments->loadMissing('user');

        $holders = [];
        foreach ($enrollments as $enrollment) {
            foreach ($enrollment->cards ?? [] as $cardId) {
                $holders[$cardId][] = $enrollment->user;
            }
        }

        foreach ($cards as $card) {
            $card->setHolders(
                collect($holders[$card->id] ?? [])->sortBy('name')
            );
        }
    }

    /**
     * Group the given models by the given parent column, the models without a
     * parent being grouped under the key 0.
     */
    private function groupByParent(Collection $models, string $column): Collection
    {
        return $models->groupBy(fn ($model) => $model->{$column} ?? 0);
    }

    /**
     * Keep the cards matching the filters that the user is allowed to list.
     */
    private function keepListableCards(
        Course $course,
        Collection $cards,
        array $filterSearchBoxes,
    ): Collection {
        // Filter specified tags id.
        $filterTags = $this->filters->get('tag');
        if ($filterTags->isNotEmpty()) {
            $cards = $cards->filter(
                fn ($card) => $card->tags
                    ->pluck('id')
                    ->intersect($filterTags)
                    ->isNotEmpty()
            );
        }

        // Filter specified states id.
        $filterStates = $this->filters->get('state');
        if ($filterStates->isNotEmpty()) {
            $cards = $cards->filter(
                fn ($card) => $filterStates->contains($card->state_id)
            );
        }

        // Filter specified holders id.
        $filterHolders = $this->filters->get('holder');
        if ($filterHolders->isNotEmpty()) {
            $cards = $cards->filter(
                fn ($card) => $card
                    ->holders()
                    ->pluck('id')
                    ->intersect($filterHolders)
                    ->isNotEmpty()
            );
        }

        // Filter specified search terms.
        $checkedBoxes = collect($filterSearchBoxes)->filter(fn ($box) => $box)->keys();

        if ($checkedBoxes->isNotEmpty() && $this->filters->get('search')->isNotEmpty()) {
            $cards = $cards->filter(
                fn ($card) => $this->matchesSearch($course, $card, $checkedBoxes)
            );
        }

        // Filter cards by user permissions.
        $user = Auth::user();

        return $cards->filter(fn ($card) => $user->can('index', $card));
    }

    /**
     * Return whether one of the search terms is found in the contents of the
     * card associated to the checked boxes.
     */
    private function matchesSearch(
        Course $course,
        Card $card,
        Collection $checkedBoxes,
    ): bool {
        // Get each contents of the card associated to the corresponding
        // checked boxes (name: title, box2: ICOR or text, etc.).
        $contents = collect([
            'name' => $card->title,
            CardBox::Box2 => match ($course->transcription) {
                // Transform ICOR transcription into plain text.
                TranscriptionType::Icor => collect([])
                    ->concat(collect($card->box2[TranscriptionType::Icor])->pluck('speaker'))
                    ->concat(collect($card->box2[TranscriptionType::Icor])->pluck('speech'))
                    ->join(''),
                default => $card->box2[TranscriptionType::Text] ?? '',
            },
            CardBox::Box3 => $card->box3 ?? '',
            CardBox::Box4 => $card->box4 ?? '',
        ]);

        // Get only the contents associated to the checked boxes.
        $contents = $contents->filter(
            fn ($value, $key) => $checkedBoxes->contains($key),
        );

        // Search for the search term in each contents.
        return $this->filters
            ->get('search')
            ->some(fn ($searchTerm) => $contents->some(
                fn ($content) => static::searchTerm($content, $searchTerm)
            ));
    }

    /**
     * Search $term inside $text. Case insensitive and without spaces.
     * HTML tags are stripped in $text.
     *
     * Return if the term is found or not.
     */
    private static function searchTerm(string $text, string $term): bool
    {
        return str_contains(
            strtoupper(str_replace(' ', '', strip_tags($text))),
            strtoupper(str_replace(' ', '', $term)),
        );
    }
}
