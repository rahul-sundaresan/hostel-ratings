<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>View Ratings</title>
        <!--Import Google Icon Font-->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!--Import materialize.css-->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
        <!--Import jQuery before materialize.js-->
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <!-- Compiled and minified JavaScript -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
        <!--Let browser know website is optimized for mobile-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <script type="text/javascript">
            $(document).ready(function() {
                $('select').material_select();
            });
        </script>
    </head>
    <body>
         <?php //get the building list from DB
            //DB creds    
            $servername = "localhost";
            $username = "test_user";
            $password = "";
            $dbname = "roomratings";
            $conn='';
            connectDB();
            $sql = "SELECT BUILDING FROM BUILDINGS";
            $resultarray = $conn->query($sql)->fetchAll(PDO::FETCH_COLUMN); 
        ?>
       <div class="container">
            
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"
              method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="input-field col s3 offset-l3">
                    <select id="building" name="building" class="inputfield">
                       <?php foreach ($resultarray as $building): ?>
                            <option value="<?php echo $building; ?>"><?php echo $building; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Building</label>
                </div> 
                <div class="input-field col s3">
                    <label>Room</label>
                    <input id="room" type="text" name="room" pattern="[a-zA-Z0-9\s]+" required
                           placeholder="BA13 or 234">
                </div>
            </div>
            <div class="row">
            <button type="submit" class="btn waves-effect waves-light">Search<i class="material-icons right">search</i></button> 
        </form>
        <form action="rating.php">
            <br><button type="submit" class="btn waves-effect waves-light">Add<i class="material-icons right">add</i></button>
        </form>
        </div>
       </div>
        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Sanitize and search
                //TODO : handle empty inputs
                //---------sanitize the inputs--------
                $building = $_POST['building'];
                $room = filter_var($_POST["room"], FILTER_SANITIZE_STRING);
                $res = getData($building, $room);
                $rescount = $res->rowcount();
                if($rescount>0) { //TODO : Use PDO:query and fetchColumn to see no. of results
                    $result = $res->fetchAll();
                    //Display the table for reviews 
                    echo '<table class="striped centered">
                            <caption>'.$building.' '.$room.'</caption>
                            <thead>
                            <tr>
                                <th>Name</th> 
                                <th>Rating</th> 
                                <th>Review</th>
                                <th>Images</th>
                            </tr>
                            </thead>';
                    echo '<tbody>';        
                    for ($x = 0; $x < $rescount; $x++) { //TODO : put this as seperate function
                        echo '<tr>';
                            echo '<td>'.$result[$x]['NAME']."</td>";
                            echo '<td>'.$result[$x]['RATING']."</td>";
                            echo '<td>'.$result[$x]['REVIEW']."</td>";
                            echo '<td><img src = "'.$result[$x]['IMG_URL'].'" alt = "" style = "width:200px; height:auto;"></td>';
                    echo '</tr>';
                    }
                    echo '</tbody>';
                    echo '</table>';
                }
                else {echo 'No reviews found';}
                $conn = '';
            }
            function getData($building,$room) {
            //get the data from DB
                global $conn;
                $stmt = $conn->prepare("SELECT * FROM reviews
                                        WHERE BUILDING = :building AND ROOM = :room
                                       ");
                $stmt->bindParam(':building', $building);
                $stmt->bindParam(':room', $room);
                $stmt->execute();
                return $stmt;
            }
            function connectDB() {
            // Connect to DB
                try {
                    global $conn,$servername,$username,$password,$dbname;//refer to 'DB Creds'
                    $conn = new PDO("mysql:host=$servername;dbname=$dbname", 
                                     $username, $password);
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                catch(PDOException $e) {
                    global $sql;
                    echo $sql . "<br>" . $e->getMessage();
                }
            }
        ?>
    </body>
</html>