// JavaScript Document
$('document').ready(function() {
/* handle form validation */
$("#cust_registration_form").validate({
rules:
{
first_name: {
required: true,
},
last_name: {
required: true,
},
home_address: {
required: true,
},
office_address: {
required: true,
},
city: {
required: true,
},
state: {
required: true,
},
country: {
required: true,
},
postalcode: {
required: true,
},
mobile: {
required: true,
},
email: {
required: true,
email: true
},
password: {
required: true,
minlength: 8,
maxlength: 15
},
confirm_password: {
required: true,
equalTo: '#password'
},
},
messages:
{
first_name: "please enter first name",
last_name: "please enter last name",
home_address: "please enter home address",
office_address: "please office address",
city: "please enter city",
state: "please enter state",
country: "please enter country",
postalcode: "please enter postalcode",
mobile: "please enter mobil number",
password:{
required: "please provide a password",
minlength: "password at least have 8 characters"
},
email: "please enter a valid email address",
confirm_password:{
required: "please retype your password",
equalTo: "password doesn't match !"
}
},
submitHandler: submitForm
});
/* handle form submit */
function submitForm() {
var data = $("#cust_registration_form").serialize();
$.ajax({
type : 'POST',
url : 'add_cust.php',
data : data,
beforeSend: function() {
$("#error").fadeOut();
$("#btn-submit").html('<span class="glyphicon glyphicon-transfer"></span>   sending ...');
},
success : function(response) {
if(response==1){
$("#error").fadeIn(1000, function(){
$("#error").html('<div class="alert alert-danger"> <span class="glyphicon glyphicon-info-sign"></span>   Sorry email already taken !</div>');
$("#btn-submit").html('<span class="glyphicon glyphicon-log-in"></span>   Create Account');
});
} else if(response=="registered"){
$("#btn-submit").html('<img src="ajax-loader.gif" />   Signing Up ...');
setTimeout('$(".form-signin").fadeOut(500, function(){ $(".register_container").load("welcome.php"); }); ',3000);
} else {
$("#error").fadeIn(1000, function(){
$("#error").html('<div class="alert alert-danger"><span class="glyphicon glyphicon-info-sign"></span>   '+data+' !</div>');
$("#btn-submit").html('<span class="glyphicon glyphicon-log-in"></span>   Create Account');
});
}
}
});
return false;
}
});