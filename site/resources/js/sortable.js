import Sortable from 'sortablejs';

// States
let states = document.getElementById('states-list');
Sortable.create(states, {
    draggable: ".drag",
    animation: 150,
    onUpdate: function(/**Event*/evt) {
        let item = evt.item;
        let stateId = parseInt(item.getAttribute('state-id'), 10);
        console.log('State id: ' + stateId);
        console.log('Old position: ' + evt.oldIndex);
        console.log('New position: ' + evt.newIndex);
    }
});
