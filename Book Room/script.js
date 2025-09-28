const buildingsGrid = document.getElementById('buildings-grid');
const roomsGrid = document.getElementById('rooms-grid');
const viewBuildings = document.getElementById('view-buildings');
const viewRooms = document.getElementById('view-rooms');
const roomsTitle = document.getElementById('rooms-title');
const backBtn = document.getElementById('back-to-buildings');
const loginBtn = document.getElementById('login-btn');
const logoutBtn = document.getElementById('logout-btn');
const loginModal = document.getElementById('login-modal');
const closeLoginModalBtn = document.getElementById('close-login-modal');
const submitLoginBtn = document.getElementById('submit-login-btn');
const usernameInput = document.getElementById('username-input');
const passwordInput = document.getElementById('password-input');
const loginMessage = document.getElementById('login-message');
const userInfo = document.getElementById('user-info');
const viewAdminPanel = document.getElementById('view-admin-panel');
const newBuildingNameInput = document.getElementById('new-building-name');
const addBuildingBtn = document.getElementById('add-building-btn');
const roomBuildingSelect = document.getElementById('room-building-select');
const newRoomNameInput = document.getElementById('new-room-name');
const addRoomBtn = document.getElementById('add-room-btn');
const bookingModal = document.getElementById('booking-modal');
const bookingModalTitle = document.getElementById('booking-modal-title');
const closeBookingModalBtn = document.getElementById('close-booking-modal');
const confirmBookBtn = document.getElementById('confirm-book-btn');
const startTimeInput = document.getElementById('start-time-input');
const endTimeInput = document.getElementById('end-time-input');
const bookingPurposeInput = document = document.getElementById('booking-purpose-input');
const messageModal = document.getElementById('message-modal');
const messageText = document.getElementById('message-text');
const closeMessageModalBtn = document.getElementById('close-message-modal');
const existingBookingsList = document.getElementById('existing-bookings-list');
const deleteBuildingBtn = document.getElementById('delete-building-btn');
const deleteRoomBtn = document.getElementById('delete-room-btn');
const buildingSelectToDelete = document.getElementById('building-select-to-delete');
const roomSelectToDelete = document.getElementById('room-select-to-delete');
const roleSelect = document.getElementById('role-select'); 

let currentBuildingId = null;
let currentRoomId = null;
let userRole = 'guest'; // Updated to 'guest' for clarity

// Initial state check
window.onload = () => {
    checkLoginStatus();
    fetchBuildings();
};

function checkLoginStatus() {
    fetch('login.php?check=true')
        .then(response => response.json())
        .then(data => {
            if (data.isLoggedIn) {
                userRole = data.role;
                updateUIForRole();
                loginBtn.classList.add('hidden');
                logoutBtn.classList.remove('hidden');
                userInfo.textContent = `User: ${data.userId} (${userRole})`;
            } else {
                userRole = 'guest';
                updateUIForRole();
                loginBtn.classList.remove('hidden');
                logoutBtn.classList.add('hidden');
                userInfo.textContent = `User: Guest`;
            }
        });
}

function fetchBuildings() {
    fetch('get_buildings.php')
        .then(response => response.json())
        .then(buildings => {
            renderBuildings(buildings);
            populateBuildingSelects(buildings);
        });
}

function fetchRooms(buildingId) {
    fetch(`get_rooms.php?building_id=${buildingId}`)
        .then(response => response.json())
        .then(rooms => {
            renderRooms(rooms);
        });
}

// UI Rendering Functions
function renderBuildings(buildings) {
    buildingsGrid.innerHTML = '';
    buildings.forEach(building => {
        const buildingCard = document.createElement('div');
        buildingCard.className = 'bg-blue-400 rounded-lg h-[120px] p-6 cursor-pointer transform hover:scale-105 transition duration-300 shadow-lg border border-gray-200';
        buildingCard.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.7)';
        buildingCard.innerHTML = `<h3 class="text-xl text-gray-800" style="color:white; font-family:times new roman; font-weight:20px; font-size:20px;">${building.name}</h3>`;
        buildingCard.onclick = () => showRooms(building.id, building.name);
        buildingsGrid.appendChild(buildingCard);
    });
}

function renderRooms(rooms) {
    roomsGrid.innerHTML = '';
    if (rooms.length === 0) {
        roomsGrid.innerHTML = `<p class="text-gray-500 text-center col-span-full">No rooms found in this building.</p>`;
        return;
    }
    rooms.forEach(room => {
        const roomCard = document.createElement('div');
        roomCard.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.5)';
        let cardBgColor = 'bg-green-400';
        let bookingInfoHTML = '<p class="text-green-700 font-semibold">Free</p>';

        const now = new Date();
        const activeBookings = room.bookings.filter(booking => new Date(booking.end_time) > now);

        if (activeBookings && activeBookings.length > 0) {
            cardBgColor = 'bg-red-400';
            bookingInfoHTML = `
                <h4 class="text-red-800 font-semibold mb-2" style="font-style:italic">Booked Times:</h4>
                <ul class="text-sm space-y-1">
                    ${activeBookings.map(booking => {
                        const start = new Date(booking.start_time);
                        const end = new Date(booking.end_time);
                        const startTime = start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const endTime = end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        return `<li class="text-gray-700">${startTime} - ${endTime} [${booking.purpose}]</li>`;
                    }).join('')}
                </ul>
            `;
        }

        roomCard.className = `rounded-lg p-4 cursor-pointer transform hover:scale-105 transition duration-300 shadow-lg border border-gray-200 flex flex-col items-center justify-center space-y-2 ${cardBgColor}`;
        
        roomCard.innerHTML = `
            <h3 class="text-lg font-semibold text-gray-800 truncate w-full text-center">${room.name}</h3>
            <div class="w-full flex-grow flex flex-col items-center justify-center text-center">
                ${bookingInfoHTML}
            </div>
        `;

        roomCard.onclick = () => openBookingModal(room.id, room.name, room.bookings);
        roomsGrid.appendChild(roomCard);
    });
}

function showBuildings() {
    viewBuildings.classList.remove('hidden');
    viewRooms.classList.add('hidden');
    fetchBuildings();
}

function showRooms(buildingId, buildingName) {
    viewBuildings.classList.add('hidden');
    viewRooms.classList.remove('hidden');
    roomsTitle.textContent = `Rooms in ${buildingName}`;
    roomsTitle.style.fontFamily = " Georgia, 'Times New Roman', Times, serif";
    roomsTitle.style.color = "rgba(3, 35, 122, 1)";
    currentBuildingId = buildingId;
    fetchRooms(currentBuildingId);
}

function updateUIForRole() {
    // Show/hide the entire admin panel based on the user's role
    if (userRole === 'admin') {
        viewAdminPanel.classList.remove('hidden');
        addBuildingBtn.classList.remove('hidden');
        deleteBuildingBtn.classList.remove('hidden');
        addRoomBtn.classList.remove('hidden');
        deleteRoomBtn.classList.remove('hidden');
    } else {
        viewAdminPanel.classList.add('hidden');
        addBuildingBtn.classList.add('hidden');
        deleteBuildingBtn.classList.add('hidden');
        addRoomBtn.classList.add('hidden');
        deleteRoomBtn.classList.add('hidden');
    }
}

function populateBuildingSelects(buildings) {
    roomBuildingSelect.innerHTML = '';
    buildingSelectToDelete.innerHTML = '<option value="">Select Building</option>';
    buildings.forEach(building => {
        const option = document.createElement('option');
        option.value = building.id;
        option.textContent = building.name;
        roomBuildingSelect.appendChild(option);

        const deleteOption = document.createElement('option');
        deleteOption.value = building.id;
        deleteOption.textContent = building.name;
        buildingSelectToDelete.appendChild(deleteOption);
    });
}

// Action Handlers
loginBtn.onclick = () => {
    loginModal.classList.remove('hidden');
};

closeLoginModalBtn.onclick = () => {
    loginModal.classList.add('hidden');
    loginMessage.textContent = '';
};

submitLoginBtn.onclick = async () => {
    const username = usernameInput.value;
    const password = passwordInput.value;
    const role = roleSelect.value; // NEW: Get the selected role
    
    const formData = new FormData();
    formData.append('username', username);
    formData.append('password', password);
    formData.append('role', role); // NEW: Append the role to the form data

    const response = await fetch('login.php', {
        method: 'POST',
        body: formData
    });
    const data = await response.json();
    if (data.success) {
        userRole = data.role;
        userInfo.textContent = `User: ${data.userId} (${userRole})`;
        loginBtn.classList.add('hidden');
        logoutBtn.classList.remove('hidden');
        loginModal.classList.add('hidden');
        updateUIForRole();
        showMessage(`Welcome, ${data.userId}! You are logged in as a ${userRole}.`);
        fetchBuildings();
    } else {
        loginMessage.textContent = data.message;
    }
};

logoutBtn.onclick = async () => {
    const response = await fetch('logout.php');
    const data = await response.json();
    if (data.success) {
        userRole = 'guest'; // Reset role to 'guest'
        updateUIForRole();
        loginBtn.classList.remove('hidden');
        logoutBtn.classList.add('hidden');
        userInfo.textContent = `User: Guest`;
        fetchBuildings();
    }
};

backBtn.onclick = () => {
    showBuildings();
};

addBuildingBtn.onclick = async () => {
    // Only allow if user is an admin
    if (userRole !== 'admin') {
        showMessage("You don't have permission to perform this action.");
        return;
    }
    const buildingName = newBuildingNameInput.value.trim();
    if (!buildingName) {
        showMessage("Building name cannot be empty.");
        return;
    }
    const formData = new FormData();
    formData.append('action', 'add_building');
    formData.append('data[name]', buildingName);

    const response = await fetch('admin_actions.php', {
        method: 'POST',
        body: formData
    });
    const data = await response.json();
    showMessage(data.message);
    if (data.success) {
        newBuildingNameInput.value = '';
        fetchBuildings();
    }
};

addRoomBtn.onclick = async () => {
    // Only allow if user is an admin
    if (userRole !== 'admin') {
        showMessage("You don't have permission to perform this action.");
        return;
    }
    const buildingId = roomBuildingSelect.value;
    const roomName = newRoomNameInput.value.trim();
    if (!buildingId || !roomName) {
        showMessage("Building and room name are required.");
        return;
    }
    const formData = new FormData();
    formData.append('action', 'add_room');
    formData.append('data[building_id]', buildingId);
    formData.append('data[room_name]', roomName);

    const response = await fetch('admin_actions.php', {
        method: 'POST',
        body: formData
    });
    const data = await response.json();
    showMessage(data.message);
    if (data.success) {
        newRoomNameInput.value = '';
        fetchBuildings();
    }
};

deleteBuildingBtn.onclick = async () => {
    // Only allow if user is an admin
    if (userRole !== 'admin') {
        showMessage("You don't have permission to perform this action.");
        return;
    }
    const buildingId = buildingSelectToDelete.value;
    if (!buildingId) {
        showMessage("Please select a building to delete.");
        return;
    }
    if (confirm("Are you sure you want to delete this building and all its rooms? This action cannot be undone.")) {
        const formData = new FormData();
        formData.append('action', 'delete_building');
        formData.append('data[building_id]', buildingId);

        const response = await fetch('admin_actions.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        showMessage(data.message);
        if (data.success) {
            fetchBuildings();
            roomSelectToDelete.innerHTML = '<option value="">Select Room</option>'; // Reset rooms dropdown
        }
    }
};

buildingSelectToDelete.addEventListener('change', async (e) => {
    const buildingId = e.target.value;
    roomSelectToDelete.innerHTML = '<option value="">Select Room</option>'; // Reset the rooms dropdown
    if (buildingId) {
        const response = await fetch(`get_rooms.php?building_id=${buildingId}`);
        const rooms = await response.json();
        rooms.forEach(room => {
            const option = document.createElement('option');
            option.value = room.id;
            option.textContent = room.name;
            roomSelectToDelete.appendChild(option);
        });
    }
});

deleteRoomBtn.onclick = async () => {
    // Only allow if user is an admin
    if (userRole !== 'admin') {
        showMessage("You don't have permission to perform this action.");
        return;
    }
    const roomId = roomSelectToDelete.value;
    if (!roomId) {
        showMessage("Please select a room to delete.");
        return;
    }
    if (confirm("Are you sure you want to delete this room? This action cannot be undone.")) {
        const formData = new FormData();
        formData.append('action', 'delete_room');
        formData.append('data[room_id]', roomId);

        const response = await fetch('admin_actions.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        showMessage(data.message);
        if (data.success) {
            const buildingId = buildingSelectToDelete.value;
            fetchBuildings();
            if (buildingId) {
                const responseRooms = await fetch(`get_rooms.php?building_id=${buildingId}`);
                const rooms = await responseRooms.json();
                roomSelectToDelete.innerHTML = '<option value="">Select Room</option>';
                rooms.forEach(room => {
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = room.name;
                    roomSelectToDelete.appendChild(option);
                });
            }
        }
    }
};

function openBookingModal(roomId, roomName, bookings) {
    // Allow booking only for admins and coordinators
    if (userRole !== 'admin' && userRole !== 'coordinator') {
        showMessage("Please log in as a coordinator or admin to book a room.");
        return;
    }
    
    bookingModalTitle.textContent = `Book/Cancel: ${roomName}`;
    currentRoomId = roomId;
    
    bookingModal.classList.remove('hidden');
    document.getElementById('booking-message').textContent = '';
    
    const now = new Date();
    const nowTime = now.toTimeString().slice(0, 5);
    const laterTime = new Date(now.getTime() + 60 * 60 * 1000).toTimeString().slice(0, 5);
    startTimeInput.value = nowTime;
    endTimeInput.value = laterTime;
    bookingPurposeInput.value = '';

    renderExistingBookings(bookings);
    checkAndPopulateCancelButton(bookings);
} 

closeBookingModalBtn.onclick = () => {
    bookingModal.classList.add('hidden');
};

function renderExistingBookings(bookings) {
    existingBookingsList.innerHTML = '';
    const now = new Date();
    const activeBookings = bookings.filter(booking => new Date(booking.end_time) > now);

    if (activeBookings.length > 0) {
        const title = document.createElement('h4');
        title.className = 'font-semibold text-blue-900 mt-4 mb-2';
        title.textContent = 'Existing Bookings:';
        existingBookingsList.appendChild(title);

        activeBookings.forEach(booking => {
            const bookingItem = document.createElement('div');
            bookingItem.className = 'bg-gray-100 p-2 rounded-lg text-sm mb-2';
            const startTime = new Date(booking.start_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const endTime = new Date(booking.end_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            bookingItem.innerHTML = `
                <p><strong>Time:</strong> ${startTime} - ${endTime}</p>
                <p><strong>Purpose:</strong> ${booking.purpose}</p>
                <p><strong>Booked by:</strong> ${booking.user_id}</p>
            `;
            existingBookingsList.appendChild(bookingItem);
        });
    }
}

confirmBookBtn.onclick = async () => {
    // Only allow if user is a coordinator or admin
    if (userRole !== 'admin' && userRole !== 'coordinator') {
        showMessage("You don't have permission to perform this action.");
        return;
    }
    const startTimeStr = startTimeInput.value;
    const endTimeStr = endTimeInput.value;
    const purpose = bookingPurposeInput.value.trim();
    const bookingMessageEl = document.getElementById('booking-message');
    
    if (!startTimeStr || !endTimeStr || !purpose) {
        bookingMessageEl.textContent = "All fields must be filled.";
        return;
    }

    const now = new Date();
    const start = new Date(`${now.toDateString()} ${startTimeStr}`);
    const end = new Date(`${now.toDateString()} ${endTimeStr}`);
    
    if (start.getTime() < now.getTime()) {
        bookingMessageEl.textContent = "Wrong time specified. Start time must be in the future.";
        return;
    }

    const durationHours = (end - start) / (1000 * 60 * 60);
    if (durationHours <= 0 || durationHours > 12) {
        bookingMessageEl.textContent = "Booking duration must be between 1 minute and 3 hours.";
        return;
    }
    
    const formattedStartTime = start.getFullYear() + '-' +
    String(start.getMonth() + 1).padStart(2, '0') + '-' +
    String(start.getDate()).padStart(2, '0') + ' ' +
    String(start.getHours()).padStart(2, '0') + ':' +
    String(start.getMinutes()).padStart(2, '0') + ':00';
    
    const formattedEndTime = end.getFullYear() + '-' +
    String(end.getMonth() + 1).padStart(2, '0') + '-' +
    String(end.getDate()).padStart(2, '0') + ' ' +
    String(end.getHours()).padStart(2, '0') + ':' +
    String(end.getMinutes()).padStart(2, '0') + ':00';
    
    const formData = new FormData();
    formData.append('room_id', currentRoomId);
    formData.append('start_time', formattedStartTime);
    formData.append('end_time', formattedEndTime);
    formData.append('purpose', purpose);

    const response = await fetch('book_room.php', {
        method: 'POST',
        body: formData
    });
    const data = await response.json();
    
    if (data.success) {
        bookingModal.classList.add('hidden');
        showMessage(data.message);
        fetchRooms(currentBuildingId);
    } else {
        bookingMessageEl.textContent = data.message;
    }
};

function checkAndPopulateCancelButton(bookings) {
    const existingCancelBtn = document.getElementById('cancel-booking-btn');
    if (existingCancelBtn) {
        existingCancelBtn.remove();
    }

    const userId = userInfo.textContent.split(' ')[1];
    const userBooking = bookings.find(b => b.user_id === userId);
    
    if (userBooking && (userRole === 'coordinator' || userRole === 'admin')) {
        const cancelBtn = document.createElement('button');
        cancelBtn.id = 'cancel-booking-btn';
        cancelBtn.className = 'w-full bg-red-500 text-white p-3 w-[200px] rounded-lg font-semibold hover:bg-red-600 transition duration-300 mt-2';
        cancelBtn.textContent = 'Cancel My Booking';
        cancelBtn.onclick = async () => {
            const formData = new FormData();
            formData.append('room_id', currentRoomId);
            const response = await fetch('cancel_booking.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                bookingModal.classList.add('hidden');
                showMessage(data.message);
                fetchRooms(currentBuildingId);
            } else {
                showMessage(data.message);
            }
        };
        confirmBookBtn.parentNode.insertBefore(cancelBtn, confirmBookBtn.nextSibling);
    }
}

function showMessage(msg) {
    messageText.textContent = msg;
    messageModal.classList.remove('hidden');
}

closeMessageModalBtn.onclick = () => {
    messageModal.classList.add('hidden');
};