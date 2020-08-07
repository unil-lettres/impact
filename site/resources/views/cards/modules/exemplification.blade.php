<!-- TODO: translations -->
<div class="card">
    <div class="card-header">
        <span class="font-weight-bolder">4. Exemplification</span>
        <button class="btn btn-primary float-right"
                id="edit-box4">
            Éditer
        </button>
    </div>
    <div class="card-body">
        <div id="rct-editor-box4"
             data='{{ json_encode(['html' => '<p>Hello World</p>Box4', 'locale' => Helpers::currentLocal(), 'editLabel' => 'Éditer', 'saveLabel' => 'Sauver']) }}'
             reference='box4'
        ></div>
    </div>
</div>
