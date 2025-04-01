
export const data = {
    breadcrumbs: [
        { label: 'Room Management', href: null }
    ],
    filterGender: [
        { value: "any", label: "Any" },
        { value: "male", label: "Male" },
        { value: "female", label: "Female" },
    ],
    sortBy: [
        { value: "name", label: "Name" },
        { value: "eligible_gender", label: "Eligible Gender" },
        { value: "beds_count", label: "Total Beds" },
        { value: "available_beds", label: "Available Beds" },
        { value: "bed_price", label: "Bed Price" },
    ],
    tableHeads: [
        "Room Name",
        "Total Beds",
        "Available Beds",
        "Status",
        "Default Eligible Gender",
        "Bed Price",
        ""
    ],
};


export const editRoomData = {
    breadcrumbs: [
        { label: 'Room Management', href: route('room.list') },
        { label: 'Edit Room', href: null }
    ],
    eligibleGenderRadioButtons: ['any', 'male', 'female'],
};

