<!-- TODO: translations -->
<div class="card">
    <div class="card-header">
        <span class="font-weight-bolder">3. Theory</span>
        <button class="btn btn-primary float-right"
                id="edit-box3">
            Éditer
        </button>
    </div>
    <div class="card-body">
        <div id="rct-editor-box3"
             data='{{ json_encode(['html' => '<p>Hello World</p>Box3', 'locale' => Helpers::currentLocal(), 'editLabel' => 'Éditer', 'saveLabel' => 'Sauver']) }}'
             reference='box3'
        ></div>
    </div>
</div>
