<?php

$username = $_SESSION['login_user'];
// SQL Insert Query of the data.
include_once '../external/connect_db.php';

$timezone= new DateTime("now", new DateTimeZone('Europe/Bucharest') );

// LAST UPLOAD DATE
$upload_date = 0;
$query = "SELECT upload_date FROM upload WHERE username = '$username' ";
$result = mysqli_query($conn, $query);
$result = mysqli_fetch_row($result);
$upload_date = $result[0];

// GET FIRST AND LAST REGISTER
$min = 0;
$max = 0;
$query = "SELECT MIN(score_date) AS min , MAX(score_date) AS max FROM user_score WHERE username = '$username' ";
$result = mysqli_query($conn, $query);
$result = mysqli_fetch_row($result);
$min = $result[0]; // first register date
$max = $result[1]; // last register date

//GET MONTHLY SCORE
$current_date= $timezone->format('Y-m-01'); // current date
$month_score = 0;
$query = "SELECT score AS month_score FROM user_score WHERE score_date = '$current_date' and username = '$username'   ";
$result = mysqli_query($conn,$query);
$count = mysqli_num_rows($result);
  if($count == 1) {
    $result = mysqli_fetch_row($result);
    $month_score = $result[0]; } // monthly score
 else {
    //echo "No monthly score has been registered\n ";
  }

  //GET LAST 12 MONTHS SCORE FOR EACH MONTH
  $time = new DateTime('now');
  $tempdate = $time->modify('-1 year')->format('Y-m-01');
  $query = "SELECT score AS months_score,score_date AS months_date  FROM user_score WHERE score_date >= '$tempdate' and username = '$username'   ";
  $query= mysqli_query($conn, $query);
  $months_score = Array();
  $months_date = Array();
  //if ($result = $query->fetch_assoc()) {
   while($result = $query->fetch_assoc()){
     //echo "Last 12 months score exists\n" ;
     $months_score[] = $result['months_score'];
     $months_date[]  = $result['months_date']; } //} // last 12 months score

  //else  {
      //echo "No 12 months score has been registered\n";
    //}

    // Fill the missing scores of each month
    $size_month_score = sizeof($months_score);
    while( $size_month_score < 12){
      array_push($months_score, 0);
      $size_month_score++;
    }

    // Create array of the last 12 months
    $year_months = array(
      strval(date("F", strtotime("0 months"))) => 0,
      strval(date("F", strtotime("-1 months"))) => 0,
      strval(date("F", strtotime("-2 months"))) => 0,
      strval(date("F", strtotime("-3 months"))) => 0,
      strval(date("F", strtotime("-4 months"))) => 0,
      strval(date("F", strtotime("-5 months"))) => 0,
      strval(date("F", strtotime("-6 months"))) => 0,
      strval(date("F", strtotime("-7 months"))) => 0,
      strval(date("F", strtotime("-8 months"))) => 0,
      strval(date("F", strtotime("-9 months"))) => 0,
      strval(date("F", strtotime("-10 months"))) => 0,
      strval(date("F", strtotime("-11 months"))) => 0
    );

    // Check if value key of year_months (array) is equal with the value of the array months_date,
    // if yes, then change the specific value of the years_month.
    for($count = 0; $count < sizeof($months_date); $count++){
      $check_key = strval(date("F", strtotime($months_date[$count])));
      if (array_key_exists($check_key,$year_months)){
        $year_months[$check_key] = $months_score[$count];
      }
    }

      // TOP 3 Leaderboard
      $query = "SELECT score AS mscore, username AS user FROM user_score WHERE score_date = '$current_date' ORDER by mscore DESC  ";
      $query= mysqli_query($conn, $query);
      $mscore = Array();
      $user = Array();
      $count=0;
      $check=0;
      //echo "TOP 3 leaderboard\n" ;
       while($result = $query->fetch_assoc()){
         $mscore[] = $result['mscore'];
         $user[] = $result['user'];
         if ($user[$count]== $username){
           $user_score=$mscore[$count];
           $check=1;
         }
         $count++;
       }
     if ($count != 0 ){
     $i=0;
     for($i=0; $i < $count; $i++){
     //echo "$mscore[$i]  $user[$i]\n " ;   // TOP 3
     //$i++ ;
   } }
     else {
       //echo " - ";
     }

     // timestampMs
     $query ="SELECT timestampMs FROM user_data WHERE username = '$username' ";
     $query= mysqli_query($conn, $query);
     $timestampMs = Array();
     while($result = $query->fetch_assoc()){
       $timestampMs[] = $result['timestampMs']; // timestampMs from DB
     }
    /* Select specific range of dates and show the analysis of user data.  */
    $months = array(
      "January" => 1,
      "February" => 2,
      "March" => 3,
      "April" => 4,
      "May" => 5,
      "June" => 6,
      "July" => 7,
      "August" => 8,
      "September" => 9,
      "October" => 10,
      "November" => 11,
      "December" => 12
    );


    if($_SERVER['REQUEST_METHOD'] == "POST") {
      if(empty($_POST['f_month']) || empty($_POST['f_year']) || empty($_POST['u_month']) || empty($_POST['u_month'])){
        $min_time=0;
        $max_time=0;
      }else{

        $f_month = $_POST['f_month'];
        $f_year = $_POST['f_year'];
        $u_month = $_POST['u_month'];
        $u_year = $_POST['u_year'];

        // Check if user input dates are valid.
        if ($f_year > $u_year || $months[$f_month] > $months[$u_month] ){
          echo "<script type='text/javascript'>alert('Wrong range of dates! Choose again.');</script>";
          $min_time=0;
          $max_time=0;
        }else{
          $from_ts = strtotime(" $f_month $f_year");
          $until_ts = strtotime(" $u_month $u_year");
          $min_time=$from_ts;
          $max_time=$until_ts;
        }
      }
    }
    else{
      $min_time=0;
      $max_time=0;
    }


    $week_days = array(' - ','Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
   $hours_1 =  Array('-',
   '12:00 am',
   '1:00 am',
   '2:00 am',
   '3:00 am',
   '4:00 am',
   '5:00 am',
   '6:00 am',
   '7:00 am',
   '8:00 am',
   '9:00 am',
   '10:00 am',
   '11:00 am',
   '12:00 pm',
   '1:00 pm',
   '2:00 pm',
   '3:00 pm',
   '4:00 pm',
   '5:00 pm',
   '6:00 pm',
   '7:00 pm',
   '8:00 pm',
   '9:00 pm',
   '10:00 pm',
   '11:00 pm'
) ;

     //activities
     $query ="SELECT activity FROM user_data WHERE username = '$username' and  timestampMs > '$min_time' and timestampMs < '$max_time' ";
     $query= mysqli_query($conn, $query);
     $activity = Array();
     while($result = $query->fetch_assoc()){
       $activity[] = $result['activity']; //activity from DB
     }
     // eggrafes ana eidos pososta
     $i=0;
     $on_foot=0;
     $walking=0;
     $on_bicycle=0;
     $running=0;
     $in_vehicle=0;
     $in_rail_vehicle=0;
     $still=0;
     $tilting=0;
     $unknown=0;
     $in_road_vehicle=0;
    // $hour = array_fill(0,10,0);
     $hour =array_fill(0, 25, array_fill(0, 11, 0));
     $day =array_fill(0, 8, array_fill(0, 11, 0));
     $maxh=array();

     for( $i = 0 ; $i < sizeof($activity); $i++ ) {
       $seconds = $timestampMs[$i]/ 1000;
       $hours = date("H",$seconds);// ena apo ta duo einai swsto
       //$hours = date("H",$timestampMs[$i]);
       //$days= date('N', $timestampMs[$i]);\
       $days= date('N', $seconds);// days of week in  number
       $hours = (int)$hours; // 06 -> 6
      if ($activity[$i]=='ON_FOOT'){
        $on_foot++;
        $hour[$hours][1]++;
        $day[$days][1]++;
      }
      elseif ($activity[$i] == 'WALKING') {
        $walking++;
        $hour[$hours][2]++;
        $day[$days][2]++;
      }
      elseif ($activity[$i] == 'ON_BICYCLE') {
        $on_bicycle++;
        $hour[$hours][3]++;
        $day[$days][3]++;
      }
      elseif ($activity[$i] == 'RUNNING') {
        $running++;
        $hour[$hours][4]++;
        $day[$days][4]++;
      }
      elseif ($activity[$i] == 'IN_VEHICLE'){
        $in_vehicle++;
        $hour[$hours][5]++;
        $day[$days][5]++;
      }
      elseif ($activity[$i] == 'IN_RAIL_VEHICLE') {
        $in_rail_vehicle++;
       $hour[$hours][6]++;
       $day[$days][6]++;
     }
      elseif ($activity[$i] == 'STILL') {
        $still++;
        $hour[$hours][7]++;
        $day[$days][7]++;
      }
      elseif ($activity[$i] == 'TILTING') {
        $tilting++;
        $hour[$hours][8]++;
        $day[$days][8]++;
      }
      elseif ($activity[$i] == 'UNKNOWN') {
        $unknown++;
        $hour[$hours][9]++;
        $day[$days][9]++;
      }
      elseif ($activity[$i] == 'IN_ROAD_VEHICLE'){
        $in_road_vehicle++;
        $hour[$hours][10]++;
        $day[$days][10]++;
      }
     }
     if ($i!=0){
     $on_foot=($on_foot/$i)*100 ;
     $walking=($walking/$i)*100;
     $running=($on_bicycle/$i)*100;
     $running=($running/$i)*100;
     $in_vehicle=($in_vehicle/$i)*100;
     $in_rail_vehicle=($in_rail_vehicle/$i)*100;
     $still=($still/$i)*100 ;
     $tilting=($tilting/$i)*100 ;
     $unknown=($unknown/$i)*100 ;
     $in_road_vehicle=($in_road_vehicle/$i)*100;
    }

    // MAX SCORE HOUR
    for( $i = 1 ; $i <=10; $i++ ){
      $maxh[$i]='-';
      if($hour[1][$i]!=0){ $maxh[$i]=1;}
      for ( $j = 1 ; $j <24; $j++ ){
       if($hour[$j][$i] < $hour[$j+1][$i] ){
        $maxh[$i]=$j+1;
        $maxh[$i]=$hours_1[$maxh[$i]+1];
        //$maxh[$i] = date('H',$maxh[$i]);
       }
      }
    }
    //MAX SCORE DAY
    for( $i = 1 ; $i <=10; $i++ ){
      $maxd[$i]=' ';
      if($day[1][$i]!=0){$maxd[$i]=$week_days[1];}
      for ( $j = 1 ; $j <7; $j++ ){
       if($day[$j][$i] < $day[$j+1][$i] ){
        $maxd[$i]=$week_days[$j+1];
       }
      }
    }

    //=================== ADMIN ==================
 //    $query ="SELECT username FROM users ";
 //    $query= mysqli_query($conn, $query);
 //    $user = Array();
 //    while($result = $query->fetch_assoc()){
 //      $user[] = $result['username']; //activity from DB
 //    }
 //
 //   //$regcount = Array();
 //   for( $i = 1 ; $i < sizeof($user); $i++ ){
 //    $regcount[$i] = 0;
 //    $registers = Array();
 //    $query ="SELECT timestampMs FROM user_data WHERE username = '$user[$i]'";
 //    $query= mysqli_query($conn, $query);
 //    while($result = $query->fetch_assoc()){
 //      //$timestampMs[] = $result['timestampMs']; // activity from DB
 //      $regcount[$i]++; //833 swsto
 //    }
 //   }
 //
 //   // $totalcount=0;
 //   // $query ="SELECT timestampMs FROM user_data ";
 //   // $query= mysqli_query($conn, $query);
 //   // while($result = $query->fetch_assoc()){
 //   //   $timestampMs[] = $result['timestampMs']; // activity from DB
 //   //   $totalcount++;
 //   // }
 //
 //  // $seconds = $timestampMs[]/ 1000;
 //   //$tmp= date("Y-m-01", $seconds);
 // $week_days = array(' - ','Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
 // //$current_date= $timezone->format('Y-m-01'); // current date
 //  $reghours=array_fill(1, 24, 0);
 //  $regdays=array_fill(1, 7, 0);
 //  $regmonths=array_fill(1, 12, 0);
 //  $regyears=array_fill(0, 10, 0); // 2010 me 2020
 //  for( $i = 0 ; $i < sizeof($timestampMs); $i++ ) {
 //   $seconds = $timestampMs[$i] / 1000;
 //   //$tmp= date("Y-m-d", $seconds);
 //   $hour = date("H",$seconds); // swsta!!! // se 06 h wra
 //   $day  = date('N',$seconds); // se noumero h mera ths evdomadas
 //   $year = date("Y", $seconds); // year
 //   $month = date("m", $seconds);
 //   //$year= (int)$year;
 //   $tyear = $year - 2000;
 //   //$hour = (int)$hour;
 //   //$day = (int)$day;
 //   $month = (int)$month;
 //
 //   for( $j = 1 ; $j <= 24; $j++ ){
 //     if ($hour==$j){ $reghours[$j]++;} }
 //   for( $j = 1 ; $j <= 7; $j++ ){
 //     if ($day==$j){ $regdays[$j]++;} }
 //   for( $j = 1 ; $j <= 12; $j++ ){
 //     if ($month==$j){ $regmonths[$j]++;} }
 //   for( $j = 1 ; $j <= 10; $j++ ){
 //     if ($tyear==($j+10)){ $regyears[$j]++;} }
 //   }
 //
 //   for( $j = 1 ; $j <= 24; $j++ ){
 //     ($reghours[$j]/sizeof($timestampMs)*100;}
 //   for( $j = 1 ; $j <= 7; $j++ ){
 //     ($regdays$j]/sizeof($timestampMs)*100}
 //   for( $j = 1 ; $j <= 12; $j++ ){
 //     ($regmonths[$j]/sizeof($timestampMs)*100}
 //   for( $j = 1 ; $j <= 10; $j++ ){
 //     ($regyears[$j]/sizeof($timestampMs)*100}


?>
