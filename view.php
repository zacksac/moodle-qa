<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of qasystem
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_qasystem
 * @copyright  2016 bharat katoch (zacksac@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace qasystem with the name of your module and remove this line.

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
$PAGE->requires->jquery();
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... qasystem instance ID - it should be named as the first character of the module.


$dbconnect = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die('unable to connect to sql server');
$dbselect  = mysql_select_db($CFG->dbname) or die('unable to select the moodle database');

if ($id) {
    $cm         = get_coursemodule_from_id('qasystem', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $qasystem  = $DB->get_record('qasystem', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $qasystem  = $DB->get_record('qasystem', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $qasystem->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('qasystem', $qasystem->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_qasystem\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $qasystem);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/qasystem/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($qasystem->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('qasystem-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($qasystem->intro) {
    echo $OUTPUT->box(format_module_intro('qasystem', $qasystem, $cm->id), 'generalbox mod_introbox', 'qasystemintro');
}
/* generate qa tables */
 $table_y="CREATE TABLE IF NOT EXISTS qa_questions(
          id int(11) NOT NULL AUTO_INCREMENT,
          qa_id int(11) NOT NULL,
          userid int(11) NOT NULL,
          cmid int(11) NOT NULL,
          group_id varchar(11) NOT NULL,
          question varchar(11) NOT NULL,
          option1 varchar(11) NOT NULL,
          option2 varchar(11) NOT NULL,
          option3 varchar(11) NOT NULL,
          option4 varchar(11) NOT NULL,
          rightans varchar(11) NOT NULL,
          timestart timestamp DEFAULT 0 NOT NULL,
          timeend timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
 $run_y=mysql_query($table_y);

/* end of generating qa tables */


// Replace the following lines with you own code.
echo 'Welcome : ' . $USER->firstname;
?>
<input type="hidden" name="cm_id" value="<?php echo $id;?>">
             <input type="hidden" name="qa_name" value="<?php echo $qasystem->name;?>">
             <input type="hidden" name="user_id" value="<?php echo $USER->id;?>">
<br>
<div id="stats">
Added questions : <span id='qcount'><?php
 $qry_c="select * from qa_questions where userid=$USER->id and cmid=$id";
       $run_c=mysql_query($qry_c);
       $count=mysql_num_rows($run_c);
       echo $count;
?>
</span>
</div>
<hr>
<button onclick="addques();"> I have question </button>
<button onclick="getques();"> My questions </button>
<button onclick="rateques();"> I want to rate questions </button>
<div id="mainwindo">
    
</div>
<?php

// Finish the page.
echo $OUTPUT->footer();


?>
<script>

function addques(){
    console.log("add ques");
    var ques="Enter your question <input name='ques' type=text><br>";
    var option1="Enter answer option 1 <input name='q1' type=text> Check the correct answer <input type=radio value='1' name='correctans'><br>";
    var option2="Enter answer option 2 <input name='q2' type=text> Check the correct answer <input type=radio value='2' name='correctans'><br>";
    var option3="Enter answer option 3 <input name='q3' type=text> Check the correct answer <input type=radio value='3' name='correctans'><br>";
    var option4="Enter answer option 4 <input name='q4' type=text> Check the correct answer <input type=radio value='4' name='correctans'><br>";
    var savebutton="<button onclick='submitques();'>Save your question</button>";

    jQuery("#mainwindo").html(ques+option1+option2+option3+option4+savebutton);



}
function submitques(){
    
    var ques=$('input[name="ques"]').val();
    var q1=$('input[name="q1"]').val();
    var q2=$('input[name="q2"]').val();
   var q3=$('input[name="q3"]').val();
   var q4=$('input[name="q4"]').val();
   if(!ques || !q1 || !q2 || !q3 || !q4 )
   {
    console.log("please fill all the fields");
    return 0;
   }
   var rightans=$("input:radio[name ='correctans']:checked").val();
   console.log(ques+q1+q2+q3+q4+rightans)
    var cm_id=$('input[name="cm_id"]').val();
            var user_id=$('input[name="user_id"]').val();
            var qa_name=$('input[name="qa_name"]').val();
          

          $.ajax({
               url: 'qa_api.php',
               data: {
                  method: 'submit_ques',
                  ques:ques,
                  q1:q1,
                  q2:q2,
                  q3:q3,
                  q4:q4,
                  rightans:rightans,
                  cm_id:cm_id,
                  user_id:user_id
               },
               error: function() {
                  $('.request_status').html('<p>An error has occurred</p>');
               },
             
               success: function(data) {
                
                var obj = jQuery.parseJSON(data);
                
                    if(obj.type=='error')
                      {
                        $('#mainwindo').html("<label>"+obj.message+"</label>");
                      }
                      else 
                       {
                        console.log(obj);
                        $('#mainwindo').html('<h1>Your question has been added </h1>');
                        $('#qcount').html(obj.qcount);
                       }
                 
                
               },
               type: 'GET'
            });



}

function getques(){
  var cm_id=$('input[name="cm_id"]').val();
  var user_id=$('input[name="user_id"]').val();

   $.ajax({
               url: 'qa_api.php',
               data: {
                  method: 'get_ques',
                 
                  cm_id:cm_id,
                  user_id:user_id
               },
               error: function() {
                  $('.request_status').html('<p>An error has occurred</p>');
               },
             
               success: function(data) {
                
                var obj = jQuery.parseJSON(data);
                
                    if(obj.type=='error')
                      {
                        $('#mainwindo').html("<label>"+obj.message+"</label>");
                      }
                      else 
                       {
                        console.log(obj);
                        $('#mainwindo').html(obj.html);
                       }
                 
                
               },
               type: 'GET'
            });
           
}


function rateques(){
  var cm_id=$('input[name="cm_id"]').val();
  var user_id=$('input[name="user_id"]').val();
  var qcount=parseInt($('#qcount').html());

   $.ajax({
               url: 'qa_api.php',
               data: {
                  method: 'rate_ques',
                 
                  cm_id:cm_id,
                  qcount:qcount,
                  user_id:user_id
               },
               error: function() {
                  $('.request_status').html('<p>An error has occurred</p>');
               },
             
               success: function(data) {
                
                var obj = jQuery.parseJSON(data);
                
                    if(obj.type=='error')
                      {
                        $('#mainwindo').html("<label>"+obj.message+"</label>");
                      }
                      else 
                       {
                        console.log(obj);
                        $('#mainwindo').html(obj.html);
                        acomp();
                       }
                 
                
               },
               type: 'GET'
            });
           
}



function x(a){
var x=$('#qcount').html()
var scale=parseInt(x);

var newval=parseInt(0);
$('.rate').find('input').each(function(){
var pval=parseInt($(this).val());
  console.log(pval);
  if (!isNaN(pval)) newval += pval; 

})
console.log(newval);
  if(newval<scale)
  {
    var pointsleft=scale-newval;
    $('.error').html('Points Left ' +pointsleft);
  }
  else
  {
    if(newval==scale)
    {
      $('.error').html('all points allocated');
      $('.rate').find('input').each(function(){
        var pval=parseInt($(this).val());
         if (isNaN(pval))
         {
          $(this).val(0);
         }
      })
    }
    else
    {
    $('.error').html("sorry the value exceeds scale");
    a.val('');
    }
    }
  console.log(a)
}


function acomp(){
$('.rate').find('input').change(function(){
x($(this));
})
}




</script>

