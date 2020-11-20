<?php

namespace App\Enums;

final class StatePermission
{
    const None = 0;
    const EditorsCanShow = 1; // Private State
    const TeachersCanShowAndEditEditorsCanShow = 2; // Archived state
    const EditorsCanShowAndEdit = 3;
    const TeachersAndEditorsCanShowAndEdit = 4;
    const AllCanShowTeachersAndEditorsCanEdit = 5;
    const AllCanShowTeachersCanEdit = 6;
    const TeachersCanShowAndEdit = 7;
}
