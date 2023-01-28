<?php 
/*
 * Copyright 2012 Sean Proctor
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

if(!defined('IN_PHPC')) {
       die("Intento de hackeo");
}

function user_create() {
	global $vars;

        if(!is_admin()) {
                return tag('div', __('Permission denied'));
        }

	if(!empty($vars['submit_form']))
		process_form();

	return display_form();

}

function display_form() {
	global $phpc_script, $phpc_token, $phpcdb, $vars;

	$groups = array();
	foreach($phpcdb->get_groups() as $group) {
		$groups[$group['gid']] = $group['name'];
	}

	$size = sizeof($groups);
	if($size > 6)
		$size = 6;

	return tag('form', attributes("action=\"$phpc_script\"", 'method="post"'),
			tag('table', attributes("class=\"phpc-container\""),
				tag('caption', 'CREAR/MODIFICAR USUARIO'),
				tag('tfoot',
					tag('tr',
						tag('td', attributes('colspan="2"'),
							create_hidden('phpc_token', $phpc_token),												
							create_hidden('action', 'user_create'),												
							create_hidden('submit_form', 'submit_form'),
							create_submit(!empty($vars['ACTUALIZAR'])?"ACTUALIZAR":"REGISTRAR")))),
				tag('tbody',										
					tag('tr',						
						tag('th', __('User Name')),
						tag('td', create_text('user_name',empty($vars['user_name']) ? "" : $vars['user_name'])),
						tag('td',create_submit('BUSCAR'))),
					tag('tr',
						tag('th', __('Password')),
						tag('td', create_password('password1'))),
					tag('tr',
						tag('th', __('Confirm Password')),
						tag('td', create_password('password2'))),
					tag('tr',
						tag('th', __('Nombre completo')),
						tag('td', create_text('nombre_completo',empty($vars['nombre_completo'])?"":$vars['nombre_completo']))),						
					tag('tr',
						tag('th', __('Correo electrónico')),
						tag('td', create_text('email',empty($vars['email'])?"":$vars['email']))),
					tag('tr',
						tag('th', __('Make Admin')),
						tag('td', create_checkbox('make_admin', '1', !empty($vars['admin'])?true:false, __('Admin')))),
					tag('tr',
						tag('th', __('Groups')),
						tag('td', create_select('groups[]',	$groups, false, attrs('multiple', "size=\"$size\""))))
				   )));
}

function process_form()
{
	global $phpcid, $vars, $phpcdb, $phpc_script;

	verify_token();

	if($_POST['submit']=="BUSCAR")
	{
		$usuario;
		if($usuario = $phpcdb->get_user_by_name($vars["user_name"]))
		{
		 	$vars['user_name'] = $usuario->get_username();
		 	$vars['nombre_completo'] = $usuario->get_nombre_completo();
		 	$vars['email'] = $usuario->get_email();
		 	if($usuario->isAdmin())
		 	  $vars['admin'] = true;
		 	$vars['ACTUALIZAR']=true; /*bandera para indicar actualizacion*/
		 	return message('Usuario encontrado: '.$usuario->get_uid());
		 }
		 else
		 {
		 	return message("Usuario no encontrado");
		 }
	}
	else if($_POST['submit']=="ACTUALIZAR")
	{
		
		if($vars['password1'] != $vars['password2'])
		{
           return message(__('Your passwords did not match'));
        }

        $passwd = empty($vars['password1'])?"SC":md5($vars['password1']);

        $usuario = $phpcdb->get_user_by_name($vars["user_name"]);
        if(empty($usuario))
        	return message("Usuario inválido");
		$vars['uid'] = $usuario->get_uid();

		$make_admin = empty($vars['make_admin']) ? 0 : 1;
		$phpcdb->actualizarUsuario($vars['uid'], $vars['user_name'], $passwd, $make_admin, $vars['nombre_completo'], $vars['email']);
		
		if(!empty($vars['groups'])){
			$phpcdb->quitar_grupos($vars['uid']); /*quitamos todos los grupos que tenia asignado para asignar si es el caso*/
			foreach($vars['groups'] as $gid)
			{
			  $phpcdb->user_add_group($vars['uid'], $gid);
			}
		}
		return message("Usuario actualizado");
	}

        if(empty($vars['user_name'])) {
                return message(__('You must specify a user name'));
        }

        if(empty($vars['password1'])) {
                return message(__('You must specify a password'));
        }

        if(empty($vars['password2']) || $vars['password1'] != $vars['password2']){
                return message(__('Your passwords did not match'));
        }

        if(empty($vars['nombre_completo'])) {
                return message('Ingresa un nombre válido');
        }

        if(empty($vars['email']) || !(filter_var($vars['email'], FILTER_VALIDATE_EMAIL))){
                return message('Ingresa un e-mail válido');
        }



	$make_admin = empty($vars['make_admin']) ? 0 : 1;

        $passwd = md5($vars['password1']);

	if($usuario = $phpcdb->get_user_by_name($vars["user_name"]))
		return message('Este usuario ya existe: '.$usuario->get_uid());
	
	$uid = $phpcdb->create_user($vars["user_name"], $passwd, $make_admin, $vars["nombre_completo"], $vars["email"]);

	if(!empty($vars['groups'])) {
		//$phpcdb->quitar_grupos()
		foreach($vars['groups'] as $gid) {
			$phpcdb->user_add_group($uid, $gid);
		}
	}

	unset($vars['user_name']);
	unset($vars['nombre_completo']);
	unset($vars['email']);

        return message('Usuario agregado correctamente: '.$uid);
}

?>
