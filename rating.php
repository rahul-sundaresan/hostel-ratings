<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Add Review</title>
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
              method="post" enctype="multipart/form-data" class="col s12">
            
                <div class="row">
                    <div class="input-field col s3 offset-l3">
                        <label for="name">Name</label>
                        <input id="name" type="text" class="pure-input-1-5" placeholder="Tony"
                           pattern="[a-zA-Z\s]+" name="name" required>
                    </div>
                    <div class="input-field col s3">
                        <label for="email">Email</label>
                        <input id="email" type="email" class="pure-input-1-5" placeholder="someone@college.domain"
                               name="email" required>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s3 offset-l3">
                        <select name="building">
                            <?php foreach ($resultarray as $building): ?>
                                <option value="<?php echo $building; ?>"><?php echo $building; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-field col s3">
                        <input id="room" type="text" name="room" placeholder="BA13 or 234" 
                           pattern="[a-zA-Z0-9\s]+" required>
                    </div>
                </div>
                    <div class="row">
                        <div class="col s6">
                            Rating on 5:
                            <div class="input-field inline">
                                <input id="rating" type="number" name="rating"  min="1" 
                                   max="5" required>
                            </div>
                        </div>
                    </div>
                <div class="row">
                    <label for="review">Review</label>
                    <div class="input-field col s12">
                        <textarea id="review" name="review" placeholder="Optional"
                               class="materialize-textarea"></textarea><br>
                    </div>
                </div>
            <div class="row">
                    <div class="file-field input-field">
                        <div class="btn">
                            <span>File</span>
                            <input id="image" type="file" name="reviewImage" accept="image/*">
                        </div>
                        <div class="s3">
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>
                    </div>
            <button class="btn waves-effect waves-light" type="submit" name="action">Submit
                <i class="material-icons right">send</i>
            </button>
            </div>
                 
          
        </form>
        </div>
       
        <?php
        /**
         * After button press
         */
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                
                
                //TODO : handle empty inputs
                //---------sanitize the inputs--------
                $building = $_POST['building'];
                $room = filter_var($_POST["room"], FILTER_SANITIZE_STRING);
                $name = preg_replace('/[0-9]/', '', $_POST["name"]);//TODO : throw error rather than silently replacing
                $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
                $rating = filter_var($_POST["rating"],FILTER_SANITIZE_NUMBER_INT);//sanitize the rating
               
                //if user inssists on posting values not in 1-5
                if ($rating < 0)
                    $rating = 0; //rating <0 become 0 or >5 become 5
                else if ($rating > 5)
                    $rating = 5;
                
                $review = filter_var($_POST["review"], FILTER_SANITIZE_STRING);
                $imageUrl = "";
                if (isValidImage($_FILES['reviewImage']['tmp_name'])) { //If image is valid, move it and assign a url
                    $uploads_dir = getcwd()."\images";
                    $img_prefix = "IMG_";
                    $tmp_name = $_FILES['reviewImage']["tmp_name"];
                    $filename = uniqid($img_prefix, true).basename($_FILES['reviewImage']['name']); //directory+random name + extention
                    if (move_uploaded_file($tmp_name, "$uploads_dir\$filename")) {
                        $imageUrl = "images\$filename";
                    }
                }
                
                if(addToDB($building, $room, $name, $email, $rating, $review, $imageUrl)) {
                //adding to DB
                echo 'Added Successfully';
                }
                $conn = null;
            }
            function connectDB() {
            /*
             * Connect to DB
             */
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
           function isValidImage($path){
                $a = getimagesize($path);
                $image_type = $a[2];
                if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG,
                IMAGETYPE_PNG, IMAGETYPE_BMP))) {
                    return true;
                } else {
                    return false;
                }           
           }
           function addToDB($building,$room,$name,$email,$rating,$review,$img_url) {
           /** Adds passed data to DB
           */  
               global $conn;
               $stmt = $conn->prepare("INSERT INTO reviews (BUILDING,ROOM,NAME,
                                       EMAIL,RATING,REVIEW,IMG_URL) 
                                       VALUES (:building, :room, :name, :email,
                                               :rating, :review, :img_url)");
                $stmt->bindParam(':building', $building);
                $stmt->bindParam(':room', $room);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':rating', $rating);
                $stmt->bindParam(':review', $review);
                $stmt->bindParam(':img_url', $img_url);
                return $stmt->execute();
           }
        ?>
    </body>
</html>