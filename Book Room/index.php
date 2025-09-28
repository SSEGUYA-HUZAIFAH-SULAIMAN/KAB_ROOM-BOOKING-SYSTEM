<?php
session_start();

$servername = "127.0.0.1:3309";
$db_username = "root";
$db_password = "";
$dbname = "kab_booking_system";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password']; 
    $password_confirm = $_POST['password_confirm'];
    $role = $_POST['role'];
    $program = htmlspecialchars($_POST['program']);
    $year = $_POST['year'];
    $email = htmlspecialchars($_POST['email']);
    $contact = htmlspecialchars($_POST['contact']);

    if (empty($name) || empty($username) || empty($password) || empty($password_confirm) || empty($role) || empty($program) || empty($year) || empty($email) || empty($contact)) {
        $message = "All fields are required.";
    } elseif ($password !== $password_confirm) {
        $message = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username or email already exists. Please choose a different one.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, username, password, role, program, year, email, contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssiss", $name, $username, $hashed_password, $role, $program, $year, $email, $contact);

            if ($stmt->execute()) {
                $message = "Registration successful! User '" . $username . "' created.";
            } else {
                $message = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

function getAllUsers($conn){
    $sql = "SELECT * FROM users";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

$users = getAllUsers($conn); 

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAB Room Booking System</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="styleyyy.css">
    <script src="styleyyy.js"></script>
    <!-- <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">-->
<style>
        .heading{
            text-align:center;
            background-color: blue;
            width: 100%;
            padding-top:2px;
            color: white;
            position: sticky;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);


        }
        .heading h1{
            font-size: 45px;
            font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; 
   }
               .heading img{
        float:left;
        width:80px;
        height:70px;
       }
       .pop{
            width: 400px;
            background-color: aliceblue;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7);
            color: black;
            padding:20px;
            border-radius: 10px;
            background-color: aqua;

       }
        .pop h3{
        margin-bottom: 25px;
            font-size: 40px;
            font-family: Georgia, 'Times New Roman', Times, serif;
    }
       .pop input{
        margin-bottom: 15px;
       }
        .pop button{
        margin-bottom: 15px;
        width: 80%;
       }

        .tabcase { 
            box-shadow: 5px -5px 10px 0 rgba(5, 0, 0, 0.5); 
            box-sizing: border-box; 
            width: 100%;
            Height:100%;
            max-height: 530px; 
            border-color: black; 
            border-radius: 7px; 
            align-items: center; 
            margin-top: 20px; 
            padding:10px;
            background-color: white; 
        }

        .table-container {
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.05);
        }

        .participants-table {
            width: 100%;
             border-collapse: collapse;
            border-radius:7px;
            background-color: #fff;
        }
               .participants-table thead th {
            background-color: #470158ff;
            color: #fff;
            padding: 0.5rem;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #555;
            white-space: nowrap;
        }

        .participants-table tbody td {
            padding: 3px;
            border-bottom: 1px solid #eee;
            color: #555;
        }
          .scrollable-table-wrapper {
            max-height: 500px;
            overflow-y: auto;
        }

        .scrollable-table-wrapper table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .scrollable-table-wrapper thead {
            position: sticky;
            top: 0;
            background-color: #5f2c2c;
            z-index: 10;
        }

        .scrollable-table-wrapper td {
            padding: 0.75rem;
            vertical-align: top;
            border: 1px solid #dee2e6;
            white-space: nowrap;
        }


</style>


    <div class="heading">
            <a href="index.php">  <img src="kab_badge.png" ></a>
            <h1><strong>KAB &nbsp ROOM &nbsp BOOKING &nbsp SYSTEM </strong></h1>
    </div>



</head>
<body  style="margin: 0; background-color: rgba(255, 255, 255, 1);">

    <header class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0" style="float: right; margin-top: 1%;">
            <div  style="float: right; display: flex; margin-top: 10px;">
                <div id="user-info" class="text-sm text-gray-600 truncate max-w-[120px] sm:max-w-none" style="font-size: large; font-style: italic; color: rgb(146, 25, 3); font-family: Georgia, 'Times New Roman', Times, serif; margin-top: 10px;"></div> &nbsp

                <button id="login-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">Login</button>   &nbsp 
                <button id="logout-btn" class="bg-gray-400 text-white px-4 py-2 rounded-lg font-semibold hover:bg-gray-500 transition duration-300 hidden">Logout</button>    &nbsp 

            </div>
        </header>
    <div id="app-container" class="w-full max-w-4xl container-bg rounded-xl p-6 sm:p-8 space-y-6" style="margin-left: auto; margin-right: auto; margin-top: 70px;  width:90%; max-width: 1200px; box-shadow: 0 5px 8px rgba(0, 0, 0, 0.6); align-items: center; text-align: center; background-color:rgba(253, 236, 2, 1)">

        <main>
            <div id="view-buildings" class="space-y-4">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-700" style="font-family: Georgia, 'Times New Roman', Times, serif;color: rgb(1, 1, 131); font-size: 40px;">BUILDINGS</h2>
                <div id="buildings-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" >

                </div>
            </div>

            <div id="view-rooms" class="hidden space-y-4">
                <button id="back-to-buildings" class="text-blue-600 font-semibold hover:underline">&larr; Back to Buildings</button>
                <h2 id="rooms-title" class="text-xl sm:text-2xl font-bold text-gray-700">Rooms in TF1</h2>
                <div id="rooms-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">

                </div>
            </div>

            <div id="view-admin-panel" class="hidden space-y-4">
               <br><hr>   
                       <div style="background-color: blue; width:300px ;  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7); text-align:center; border-radius:15px; height:40px; color:white; font-family:times new roman; justify-content:center; display:flex; align-items:center">
             <button id="AddDeleteButton" >Add & Delete Buildings/Rooms</button> </div><br>
                        <div id="AddDelete"  class="hidden w-full  p-8" style="border-radius:10px; background-color:white; float:left; width:100%; margin-left: auto; margin-right: auto;  max-width: 1200px; box-shadow: 5px 5px 9px rgba(0, 0, 0, 0.7); align-items: center; text-align: center; "> 

                                    <div style="width:100%;   margin-bottom: 20px;">
                                        <div class="p-4 bg-gray-50 rounded-lg border">
                                            <h3 class="font-semibold text-gray-800">Add New Building</h3>
                                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-2">
                                                <input type="text" id="new-building-name" class="flex-grow p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., TF7">
                                                <button id="add-building-btn" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-600 transition duration-300">Add Building</button>
                        
                                            </div>
                                        </div>
                                        <br>
                                        <div class="p-4 bg-gray-50 rounded-lg border">
                                            <h3 class="font-semibold text-gray-800">Add New Room</h3>
                                            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-2">
                                                <select id="room-building-select" class="flex-grow p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 w-[150px] focus:ring-blue-500"></select>
                                                <input type="text" id="new-room-name" class="flex-grow p-2 rounded-lg w-[150px] border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Room H">
                                                <button id="add-room-btn" class="bg-green-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-600 transition duration-300">Add Room</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="width:100%;  ">
                                            <div class="p-4 bg-gray-50 rounded-lg border">
                                                <h3 for="building-select-to-delete" class="font-semibold text-gray-800">Select Building to Delete</h3>
                                                <select id="building-select-to-delete" class=" flex-grow p-2 border rounded-lg border w-[80%] border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"></select>
                                                <button id="delete-building-btn" class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-600 transition duration-300">Delete Building</button>
                                            </div>
                                        <br>
                                            <div class="p-4 bg-gray-50 rounded-lg border">
                                                <h3 for="room-select-to-delete"  class="font-semibold text-gray-800">Select Room to Delete</h3>
                                                <select id="room-select-to-delete" class=" flex-grow p-2 border rounded-lg border w-[80%] border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"></select>
                                                <button id="delete-room-btn" class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-600 transition duration-300">Delete Room</button>
                                            </div>
                                    </div>
                        </div>
            
                <div style="float:right; width:100%"> 

        <div style="background-color: blue; width:300px ;  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7); text-align:center; border-radius:15px; height:40px; color:white; font-family:times new roman; justify-content:center; display:flex; align-items:center">
             <button id="showButton" >Register User</button> </div><br>
        <div id="registerForm" class=" hidden bg-gray-100 bg-opacity-70 backdrop-filter backdrop-blur-md p-4 rounded-xl shadow-2xl w-full max-w-md border " style="text-align:left; color:black; width:100%; ">
            <h1 class="text-3xl font-bold text-center text-black mb-6">Register a user</h1>

            <?php 
            
            if ($message): ?>
                <div class="p-3 mb-4 rounded-lg font-medium 
                <?php echo strpos($message, 'successful') !== false ? 'bg-green-800 bg-opacity-50 text-green-300' : 'bg-red-800 bg-opacity-50 text-red-300'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?> 

            <form action="index.php" method="post" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-black-900">Full Name</label>
                    <input type="text" id="name" name="name" required
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>
                <div>
                    <label for="username" class="block text-sm font-medium text-black-900">Username</label>
                    <input type="text" id="username" name="username" placeholder="Peter_BCS" required
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-black-900">Password</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-black-900">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-black-900">Role</label>
                    <select type="role" id="role" name="role" required
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                      <option value="coordinator">Coordinator</option>
                    <option value="admin">Admin</option> </select>
                        </div>
                <div>
                    <label for="program" class="block text-sm font-medium text-black-900">Program</label>
                    <input type="text" id="program" name="program" placeholder="Bachelors in Computer Science" 
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>
                                <div>
                    <label for="year" class="block text-sm font-medium text-black-900">Year</label>
                    <input type="number" id="year" name="year" 
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-black-900">Email</label>
                    <input type="email" id="email" name="email" required
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>
                                <div>
                    <label for="contact" class="block text-sm font-medium text-black-900">Contact</label>
                    <input type="text" id="contact" name="contact" required
                           class="mt-1 block w-full px-4 py-2  border border-gray-200 rounded-md text-black shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200">
                </div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white
                               bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500
                               transition-transform transform active:scale-95 duration-150">
                    Register
                </button>
            </form>
        </div> <br>

                </div>
                


        <div style="background-color: blue; width:300px ;  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.7); text-align:center; border-radius:15px; height:40px; color:white; font-family:times new roman; justify-content:center; display:flex; align-items:center">
             <button id="viewUsersButton" >View Users</button> </div><br>
                <div id="viewUsers" class="hidden">


    <?php 
        if ($users != 0) {
    ?>


    <div >

        <?php if (isset($_GET['error'])) { ?>
         <div class="alert alert-danger mt-3 n-table" 
              role="alert">
          <?=$_GET['error']?>
         </div>
        <?php } ?>

      <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-info mt-3 n-table" 
              role="alert">
          <?=$_GET['success']?>
        </div>
        <?php } ?>

        <div class="table-responsive">
          <div class="tabcase">
                      <div class="table-container">
                <div class="scrollable-table-wrapper">

                   <table class=" table table-hover participants-table">
            <thead>
                <tr style=" color:white; font-family:times; font-size: 20px">
                <th scope="col">#</th>
                <th scope="col">Full Name</th>
                <th scope="col">UserName</th>
                <th scope="col">Role</th>
                <th scope="col">Program</th>
                <th scope="col">Contact</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 0; foreach ($users as $user ) { 
                $i++;  ?>
              <tr>
                <th scope="row" style="font-style:italic"><?=$i?></th>
                <td><?=$user['name']?></a></td>
                <td><?=$user['username']?></td>
                <td><?=$user['role']?></td>
                <td><?=$user['program']?></td>
                <td><?=$user['contact']?></td>
                
                <!--<td>
                    <a href="user-edit.php?user_id=<?=$user['user_id']?>"
                       class="btn btn-warning">Edit</a>
                    <a href="user-delete.php?user_id=<?=$user['user']?>"
                       class="btn btn-danger delete-btn"
                       data-user-id="<?=$user['user_id']?>"
                       data-user-name="<?=$user['fname'] . ' ' . $user['lname']?>">Delete</a>
                </td>-->
              </tr>
            <?php } ?>
            </tbody>
          </table>
                </div>
        </div>
      <?php }else{ ?>
          <div class="alert alert-info .w-450 m-5" 
               role="alert">
            Empty!
          </div>
      <?php } ?>
    </div>
      </div>
      </div>



            </div>



            </div>
        </main>

    </div>


        <div id="login-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50">
                    
             <div class="pop">
                <h3 class="text-2xl font-bold text-gray-800 text-center">Login</h3>
                <input type="text" id="username-input" placeholder="Username" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">

                <input type="password" id="password-input" placeholder="Password" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <br>
                    <select id="role-select" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="coordinator">Coordinator</option>
                    <option value="admin">Admin</option>
                </select>
                <br><br>
                <button id="submit-login-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">Login</button>
                <button id="close-login-modal" class="w-full bg-gray-300 text-gray-800 p-3 rounded-lg font-semibold hover:bg-gray-400 transition duration-300">Cancel</button>
                <div id="login-message" class="text-red-500 text-center text-sm"></div>
            </div>
          
        </div>

        <div id="booking-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50 h-[100%]" >
            <div class="bg-white rounded-xl p-8 max-w-sm w-full space-y-4" style="width: 100%; max-width: 700px;">
                            <h3 id="booking-modal-title" class="text-2xl font-bold text-gray-800 text-center" style="color: rgb(1, 1, 150); font-family: Georgia, 'Times New Roman', Times, serif; font-size: 35px;">Book Room</h3>
            <hr>
                <div style="float: left; width: 50%;">
                <div class="space-y-2">
                    <label for="start-time-input" class="block font-semibold">Start Time:</label>
                    <input type="time" id="start-time-input" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label for="end-time-input" class="block font-semibold">End Time:</label>
                    <input type="time" id="end-time-input" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="space-y-2">
                    <label for="booking-purpose-input" class="block font-semibold">Purpose:</label>
                    <input type="text" id="booking-purpose-input" placeholder="e.g BIT2103-Lec" class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

            </div>
            <div style="float: right; width: 40%;">
                <div id="existing-bookings-list" class="space-y-2">

                </div>

            </div>

            <div style="display: flex; margin-top: 300px;width: 100%; gap:20px">
                <button id="confirm-book-btn" class="w-full bg-blue-600 text-white p-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300" style="width: 200px; height: 50px;">Confirm </button>
                <button id="close-booking-modal" class="w-full bg-gray-300 text-gray-800 p-3 rounded-lg font-semibold hover:bg-gray-400 transition duration-300" style="height: 50px; width:200px">Cancel</button>
            </div>
                <div id="booking-message" class="text-red-500 text-center text-sm"></div>

        </div>
        </div>
        <div id="message-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 z-50" >
            <div class="bg-green-200 rounded-xl p-4 max-w-xs w-full text-center space-y-4">
                <p id="message-text" class="text-lg font-semibold text-gray-800" style="font-family:monospace "></p>
                <button id="close-message-modal" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">OK</button>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
    <script>
const AddDeleteButton = document.getElementById('AddDeleteButton');
const toggleButton = document.getElementById('showButton');
const registerForm = document.getElementById('registerForm');
const AddDelete = document.getElementById('AddDelete');
const viewUsersButton = document.getElementById('viewUsersButton');
const viewUsers = document.getElementById('viewUsers');

toggleButton.addEventListener('click', () => {
  registerForm.classList.toggle('hidden');
});
AddDeleteButton.addEventListener('click', () => {
  AddDelete.classList.toggle('hidden');
});
viewUsersButton.addEventListener('click', () => {
  viewUsers.classList.toggle('hidden');
});

    </script>
</body>
</html>
