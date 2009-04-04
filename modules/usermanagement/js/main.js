function select_all(name)
{
	var theForm = name.form, z = 0;
	for(z=0; z<theForm.length;z++){
		if(theForm[z].type == 'checkbox' && theForm[z].name != 'checkall'){
			theForm[z].checked = name.checked;
		}
	}
}

function user_pass_change_cp(id, source)
{
	//alert(id + '|' + source.txtUsername.value);
	if (id == 1)
	{
		// txtUsername --> txtServerAccount
		source.txtServerAccount.value = source.txtUsername.value; 
	}
	if (id == 2)
	{
		// txtPassword --> txtServerAccountPassword
		source.txtServerAccountPassword.value = source.txtPassword.value;
	}
	//source.name.value = source.value;
}

function fixUpForSubmit(source)
{
	z = 0;
	for(z=0; z<source.length;z++){
		if(source[z].type == 'checkbox' && source[z].name != 'checkall'){
			source[z].name = source[z].id;
		}
	}
	return true;
}

function submit_form(what, page, source)
{
	source.whattodo.value = what;
	source.action = 'index.php?module=usermanagement&page='+page;
	fixUpForSubmit(source);
	source.submit();
}

function submit_confirm(option, source)
{
	source.option.value = option;
	source.submit();
}

function submit_confirm(option, source)
{
	source.option.value = option;
	source.submit();
}

function cancel_confirm(page)
{
	if (page == 1)
	{
		window.location = 'index.php?module=usermanagement';
	}
	else if (page == 2)
	{
		window.location = 'index.php?module=usermanagement&page=packages';
	}
}

function change_package(package, source)
{
	var name = source.txtName.value;
	var username = source.txtUsername.value;
	var email = source.txtEmail.value;
	var password = source.txtPassword.value;
	
	window.location = 'index.php?module=usermanagement&page=createnew&package='+package+'&name='+name+'&username='+username+'&email='+email+'&password='+password;
}

function noPackage(source)
{
	source.selectPackage.value = 'none';
}
