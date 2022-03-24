
=================================================================
Moible Login rest api 
=================================================================

------------Token---------------

POST

http://[WEBSITE_URL]/rest/V1/integration/admin/token/

Post data in json format
{
	"username":"admin",
	"password":"admin123"
}

------------Send OTP---------------

POST

http://[WEBSITE_URL]/rest/V1/mobilelogin/otp/send

Post data in json format
{
	"mobilenumber":"+918141102201",
	"otptype":"login",
	"resendotp":"0",
	"oldmobile":"0"
}


otptype = register,forgot,update,login 

POSTMAN : https://meetanshi.d.pr/wFy0vd

------------Verify OTP---------------

POST

http://[WEBSITE_URL]/rest/V1/mobilelogin/otp/verify/

Post data in json format
{
	"mobilenumber":"+918141102201",
	"otptype":"login",
	"otpcode":"680237",
	"oldmobile":"0"
}

otptype = register,forgot,update,login

POSTMAN : https://meetanshi.d.pr/DWrxtL

------------ Create Account ---------------

POST

http://[WEBSITE_URL]/rest/V1/mobilelogin/account/create

Post data in json format
{
	"mobile":"+918141102205",
	"password":"admin123",
	"firstname":"Jignesh",
	"lastname":"Parmar",
	"email":"jignesh.meetanshi@gmail.com"
}

POSTMAN : https://meetanshi.d.pr/BUUFmk


------------ Login WIth email or Mobile Number ---------------

POST

http://[WEBSITE_URL]/rest/V1/mobilelogin/account/login

Post data in json format
{
	"emailmobile":"+918141102201",
	"mobpassword":"admin123"
}

POSTMAN :  https://meetanshi.d.pr/dl3hqW

------------reset password---------------

POST

http://[WEBSITE_URL]/rest/V1/mobilelogin/otp/resetpassword/

Post data in json format
{
	"mobilenumber":"+918141102201",
	"password":"admin123"
}

POSTMAN : https://meetanshi.d.pr/Cvq1fh

















