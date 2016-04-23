<?php
/*ajax queries */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/gradelib.php');
$dbconnect = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die('unable to connect to sql server');
$dbselect  = mysql_select_db($CFG->dbname) or die('unable to select the moodle database');

if(isset($_GET['method'])){
    $postvars=$_GET; 
  
  
	$_GET['method']($postvars,$USER,$CFG);

}

function submit_ques($postvars,$USER,$CFG){
	
	$name=$postvars['qa_name'];
	$cmid=$postvars['cm_id'];
	$userid=$postvars['user_id'];
	$ques=$postvars['ques'];
	$q1=$postvars['q1'];
	$q2=$postvars['q2'];
	$q3=$postvars['q3'];
	$q4=$postvars['q4'];
	$rightans=$postvars['rightans'];
       
       $qry_ins="insert into qa_questions (qa_id,userid,cmid,group_id,question,option1,option2,option3,option4,rightans) values 
       ('',$userid,$cmid,'','$ques','$q1','$q2','$q3','$q4','$rightans')";;
       $run_ins=mysql_query($qry_ins);

       $qry_c="select * from qa_questions where userid=$userid and cmid=$cmid";
       $run_c=mysql_query($qry_c);
       $count=mysql_num_rows($run_c);
       $response['qcount']=$count;
       $response['type']='success';
       echo json_encode($response);
}

function get_ques($postvars,$USER,$CFG){
	
	
	$cmid=$postvars['cm_id'];
	$userid=$postvars['user_id'];
	

	$html=array();
       $htmlindex=1;
       $ques='';
       $qry_ins="select * from qa_questions where userid=$userid and cmid=$cmid";
       $run_ins=mysql_query($qry_ins);
       while($row=mysql_fetch_array($run_ins))
       {
        $ques.='Q.'.$htmlindex.'  '.$row['question'].'</br>';
        $ans_op='option'.$row['rightans'];
        $ques.='Ans.'.'  '.$row[$ans_op] .'<br><hr>';
          
        $html[]=$ques;
        $htmlindex++;
       }  

       $response['type']='success';
       $response['html']=$ques;
       echo json_encode($response);
}

function rate_ques($postvars,$USER,$CFG){
	
	
	$cmid=$postvars['cm_id'];
	$userid=$postvars['user_id'];
  $qcount=$postvars['qcount'];
	

	$html=array();
       $htmlindex=1;
       $ques='<h3> Rate your question on scale '.$qcount.'</h3><div class="error"></div><br>';
       $qry_ins="select * from qa_questions where userid=$userid and cmid=$cmid";
       $run_ins=mysql_query($qry_ins);

       while($row=mysql_fetch_array($run_ins))
       {
        $ques.='<div class="ques">Q.'.$htmlindex.'  '.$row['question'].'  </div> <div class="rate"><input id="'.$row['id'].'" type="number"></div>';
        $ans_op='option'.$row['rightans'];
        $ques.='<div class="ans">Ans.'.'  '.$row[$ans_op] .'</div><hr>';
          
        $html[]=$ques;
        $htmlindex++;
       }  
        $ques.="<button id='save_raing'>Save Rating</button>";
       $response['type']='success';
       $response['html']=$ques;
       echo json_encode($response);
}