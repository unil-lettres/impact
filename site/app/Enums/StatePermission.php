<?php

namespace App\Enums;

final class StatePermission
{
    const None = 0;

    const TeachersCanShowAndEditEditorsCanShow = 1; // "Archived" state

    const EditorsCanShowAndEdit = 2; // "Private" State & default state

    const TeachersAndEditorsCanShowAndEdit = 3; // "Open" state

    const AllCanShowTeachersAndEditorsCanEdit = 4; // "Public" state

    const AllCanShowTeachersCanEdit = 5;

    const TeachersCanShowAndEdit = 6;
}
