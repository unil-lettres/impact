import Sortable from 'sortablejs';
import axios from "axios";

// States
let statesList = document.getElementById('states-list');
if(statesList) {
    Sortable.create(statesList, {
        draggable: ".drag",
        animation: 150,
        onUpdate: function(evt) {
            let stateId = evt.item.getAttribute('state-id');
            let courseId = statesList.getAttribute('course-id');
            let states = Array.from(statesList.getElementsByClassName('drag'));
            let route = '/courses/' + courseId + '/state/' + stateId + '/position';

            let ids = states.map(state =>
                state.getAttribute('state-id')
            );

            updatePosition(route, ids);
        }
    });
}

function updatePosition(route, newOrder) {
    axios.put(route, {
        newOrder: newOrder
    }).then(response => {
        console.log(response);
    }).catch(error => {
        console.log(error)
    });
}
