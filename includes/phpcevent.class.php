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

class PhpcEvent {
	var $eid,
		$cid,
	 	$uid,
	 	$author,
	 	$subject,
	 	$desc,
	 	$readonly,
	 	$category,
	 	$bg_color,
	 	$text_color,
	 	$catid,
	 	$gid,
	 	$ctime,
	 	$mtime,
	 	$cal,
	 	$lugar,
	 	$invitados,
	 	$archivo_adjunto="";

	function __construct($event)
	{
		global $phpcid, $phpc_cal, $phpcdb;
		$this->eid = $event['eid'];
		$this->cid = $event['cid'];
		$this->uid = $event['owner'];
		if(empty($event['owner']))
			$this->author = __('anonymous');
		elseif(empty($event['username']))
			$this->author = __('unknown');
		else
			$this->author = $event['nombre_completo'];
		$this->subject = $event['subject'];
		$this->desc = $event['description'];
		$this->readonly = $event['readonly'];
		$this->category = $event['name'];
		$this->bg_color = $event['bg_color'];
		$this->text_color = $event['text_color'];
		$this->catid = $event['catid'];
		$this->gid = $event['gid'];
		$this->ctime = $event['ctime'];
		$this->mtime = $event['mtime'];
		$this->lugar = $event['lugar'];
		$this->invitados = $event['invitados'];
		$this->archivo_adjunto = $event['archivo_adjunto'];
		if($this->cid == $phpcid)
		{
			$this->cal = $phpc_cal;
		}
		else
		{			
			$this->cal = $phpcdb->get_calendar($this->cid);
		}
	}

	function get_raw_subject() {
		return phpc_html_escape($this->subject);
	}

	function get_subject()
	{
		if(empty($this->subject))
			return __('(No subject)');

		return phpc_html_escape(stripslashes($this->subject));
	}

	function get_author()
	{
		return $this->author;
	}

	function get_uid()
	{
		return $this->uid;
	}

	function get_raw_desc() {
		// Don't allow tags and make the description HTML-safe
		return phpc_html_escape($this->desc);
	}

	function get_desc()
	{
		return parse_desc($this->desc);
	}

	function get_eid()
	{
		return $this->eid;
	}

	function get_cid()
	{
		return $this->cid;
	}

	function is_readonly()
	{
		return $this->readonly;
	}

	function get_text_color()
	{
		return phpc_html_escape($this->text_color);
	}

	function get_bg_color()
	{
		return phpc_html_escape($this->bg_color);
	}

	function get_category()
	{
		if(empty($this->category))
			return $this->category;
		return phpc_html_escape($this->category);
	}

	function is_owner() {
		global $phpc_user;

		return $phpc_user->get_uid() == $this->get_uid();
	}

	// returns whether or not the current user can modify $event
	function can_modify()
	{
		return $this->cal->can_admin() || $this->is_owner()
			|| ($this->cal->can_modify() && !$this->is_readonly());
	}

	// returns whether or not the current user can read $event
	function can_read() {
		global $phpcdb, $phpc_user;

		$visible_category = empty($this->gid) || !isset($this->catid)
			|| $phpcdb->is_cat_visible($phpc_user->get_uid(),
					$this->catid);
		return $this->cal->can_read() && $visible_category;
	}

	function get_ctime_string() {
		return format_timestamp_string($this->ctime,
				$this->cal->date_format,
				$this->cal->hours_24);
	}

	function get_mtime_string() {
		return format_timestamp_string($this->mtime,
				$this->cal->date_format,
				$this->cal->hours_24);
	}

	function get_lugar(){
		if(empty($this->lugar))
			return __('(Sin lugar)');

		return phpc_html_escape(stripslashes($this->lugar));
	}

	function get_invitados(){
		if(empty($this->invitados))
			return __('(Sin invitados)');

		return phpc_html_escape(stripslashes($this->invitados));
	}

	function getArchivoAdjunto(){
		if(empty($this->archivo_adjunto))
			return NULL;
		else
			return 'adjuntos_evento/'.$this->archivo_adjunto;
	}

}
?>
