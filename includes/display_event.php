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

/*
   This file has the functions for the main displays of the calendar
*/

if ( !defined('IN_PHPC') ) {
       die("Hacking attempt");
}

// Full view for a single event
function display_event()
{
	global $vars;

	if(!empty($vars['contentType']) && $vars['contentType'] == 'json')
		return display_event_json();
	
	if(isset($vars['oid']))
		return display_event_by_oid($vars['oid']);
	
	if(isset($vars['eid']))
		return display_event_by_eid($vars['eid'],true);

	// If we get here, we did something wrong
	soft_error(__("Invalid arguments."));
}

function display_event_by_oid($oid)
{
	global $phpcdb, $year, $month, $day;

	$event = $phpcdb->get_occurrence_by_oid($oid);

	$eid = $event->get_eid();

	if(!$event->can_read()) {
		return tag('p', __("You do not have permission to read this event."));
	}

	$event_header = tag('div', attributes('class="phpc-event-header"'),
			tag('div',attributes('class="phpc-event-creator"'), __('by').' ',
				tag('cite', $event->get_author())));

	$category = $event->get_category();
	if(!empty($category))
		$event_header->add(tag('div',attributes('class="phpc-event-cats"'), __('Category') . ': '
					. $category));

	$event_header->add(tag('div',attributes('class="phpc-event-time"'),	__('When').": ".$event->get_datetime_string()));
	/*implementando campo LUGAR*/
	$event_header->add(tag('div',attributes('class="phpc-event-lugar"'), __('Lugar').": ".$event->get_lugar()));
	/*implementando campo INVITADOS*/
	$event_header->add(tag('div',attributes('class="phpc-event-invitados"'), __('Invitados').": ".$event->get_invitados()));
	/*implementando campo ARCHIVO_ADJUNTO*/

	if($event->getArchivoAdjunto()!=NULL)
		$event_header->add(tag('a',attributes('class="phpc-event-archivo_adjunto" target="_blank" href="'.$event->getArchivoAdjunto().'"'),'<img src="static/images/ico_archivo_adjunto.png">Click aquí'));
	
	$event_header->add(tag('div', __('Created at: '), $event->get_ctime_string()));
	if(!empty($event->mtime))
		$event_header->add(tag('div', __('Last modified at: '),
				$event->get_mtime_string()));
				
	$menu_tag = tag('div', attrs('class="phpc-bar ui-widget-content"')); 
	// Add modify/delete links if this user has access to this event.
        if($event->can_modify()) {
		$menu_tag->add(array(create_event_link("<span class='button modificar'>".__('Modify')."</span>",
						'event_form', $eid), "\n",
					create_event_link("<span class='button borrar'>".__("Delete")."</span>",
						'event_delete', $eid), "\n",
					create_occurrence_link("<span class='button modificar'>".__('Modify Occurrence')."</span>",
						'occur_form', $oid), "\n",
					create_occurrence_link("<span class='button borrar'>".__('Remove Occurrence')."</span>",
						'occurrence_delete', $oid),
					"\n"));
	}


	$occurrences = $phpcdb->get_occurrences_by_eid($eid);
		//$occurrence_div = tag('div', );
	$i = 0;
	while($i < sizeof($occurrences)) {
		if($occurrences[$i]->get_oid() == $oid)
			break;
		$i++;
	}
	// if we have a previous event
	$prev = $i - 1;
	if($prev >= 0) {
		$prev_occur = $occurrences[$prev];
		$menu_tag->add(create_occurrence_link("<span class='button info'>".__('Previous occurrence on') . " " .
					$prev_occur->get_date_string()."</span>",
					'display_event',
					$prev_occur->get_oid()), ' ');
	}
	// if we have a future event
	$next = $i + 1;
	if($next < sizeof($occurrences)) {
		$next_occur = $occurrences[$next];
		$menu_tag->add(create_occurrence_link(
					"<span class='button info'>".__('Next occurrence on') . " " .
					$next_occur->get_date_string()."</span>",
					'display_event',
					$next_occur->get_oid()), ' ');
	}

	$menu_tag->add(create_event_link("<span class='button todainfo'>".__('View All Occurrences')."</span>",
				'display_event', $eid));

	$event_header->add($menu_tag);

	$year = $event->get_start_year();
	$month = $event->get_start_month();
	$day = $event->get_start_day();

	$desc_tag = tag('div', attributes('class="phpc-desc"'),
			tag('h3', __("Description")),
			tag('p', $event->get_desc()));

	return tag('div', attributes('class="phpc-main phpc-event"'),
			tag('h2', $event->get_subject()), $event_header,
			$desc_tag);
}

function display_event_by_eid($eid, $controles)
{
	global $phpcdb, $year, $month, $day;

	$event = new PhpcEvent($phpcdb->get_event_by_eid($eid));

	if(!$event->can_read()) {
		return tag('p', __("You do not have permission to read this event."));
	}

	$event_header = tag('div', attributes('class="phpc-event-header"'),
			tag('div', __('by').' ',
				tag('cite', $event->get_author())));

	$event_header->add(tag('div', __('Created at: '), $event->get_ctime_string()));
	if(!empty($event->mtime))
		$event_header->add(tag('div', __('Last modified at: '),
				$event->get_mtime_string()));

	$category = $event->get_category();
	if(!empty($category))
		$event_header->add(tag('div', __('Category') . ': '
					. $category));

	/*implementando campo LUGAR*/
	$lugar = $event->get_lugar();
	if(!empty($lugar))
		$event_header->add(tag('div', 'Lugar' . ': '
					. $lugar));
	/*implementando campo INVITADOS*/
	$invitados = $event->get_invitados();
	if(!empty($invitados))
		$event_header->add(tag('div', 'Invitados' . ': '
					. $invitados));
	/*implementando campo ARCHIVO_ADJUNTO*/
	$adjunto = $event->getArchivoAdjunto();
	if(!empty($adjunto))
		$event_header->add(tag('div', 'Archivo adjunto' . ': <a href="'. $adjunto.'" target="_blank"><img src="static/images/ico_archivo_adjunto.png"></a>'));

	// Add modify/delete links if this user has access to this event.
        if($event->can_modify() & $controles) {
		$event_header->add(tag('div', attrs('class="phpc-bar ui-widget-content"'),
					create_event_link("<span class='button modificar'>".__('Modify')."</span>",
						'event_form', $eid), "\n",
					create_event_link("<span class='button agregar'>".__('Add Occurrence')."</span>",
						'occur_form', $eid), "\n",
					create_event_link("<span class='button borrar'>".__("Delete")."</span>",
						'event_delete', $eid)));
	}

	$desc_tag = tag('div', attributes('class="phpc-desc"'),
			tag('h3', __("Description")),
			tag('p', $event->get_desc()));

	$occurrences_tag = tag('ul');
	$occurrences = $phpcdb->get_occurrences_by_eid($eid);
	$set_date = false;
	foreach($occurrences as $occurrence) {
		if(!$set_date) {
			$year = $occurrence->get_start_year();
			$month = $occurrence->get_start_month();
			$day = $occurrence->get_start_day();
		}
		$oid = $occurrence->get_oid();
		$occ_tag = tag('li', attrs('class="ui-widget-content"'),
				create_occurrence_link($occurrence->get_date_string(). ' ' . '<b>de</b>' . ' '. $occurrence->get_time_span_string(), 'display_event', $oid));
		if($event->can_modify() & $controles) {
			$occ_tag->add(" ",
					create_occurrence_link("<span class='button modificar'>".__('Edit')."</span>", 'occur_form', $oid), " ",
					create_occurrence_link("<span class='button borrar'>".__('Remove')."</span>", 'occurrence_delete', $oid));
		}
		$occurrences_tag->add($occ_tag);
	}

	return tag('div', attributes('class="phpc-main phpc-event"'),
			tag('h2', $event->get_subject()), $event_header,
			$desc_tag, tag ('div',attributes('class="phpc-occ"'),tag('h3', __('Occurrences')),
			$occurrences_tag));
}

// generates a JSON data structure for a particular event
function display_event_json()
{
	global $phpcdb, $vars;

	if(!isset($vars['oid']))
		return "";

	$event = $phpcdb->get_occurrence_by_oid($vars['oid']);

	if(!$event->can_read())
		return "";

	$author = __("by") . " " . $event->get_author();
	$time_str = $event->get_time_span_string();
	$date_str = $event->get_date_string();

	$category = $event->get_category();
	if(empty($category))
		$category_text = '';
	else
		$category_text = __('Category') . ': ' . $event->get_category();

		if ($time_str!="") 
			$time="$date_str <br>" . __("from") . " $time_str";
		else 
			$time="$date_str ";
		
	return json_encode(array("title" => $event->get_subject(),
				"author" => $author,
				"time" => $time,
				"category" => $category_text,
				"lugar" => "Lugar: ".$event->get_lugar(),
				"invitados" => "Invitados: ".$event->get_invitados(),
				"archivo_adjunto" => $event->getArchivoAdjunto(),
				"body" => $event->get_desc()));
}

?>
