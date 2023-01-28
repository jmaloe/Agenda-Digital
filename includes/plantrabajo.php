<?php
if(!defined('IN_PHPC')) {
	die("Hacking attempt");
}

function plantrabajo()
{
	return process_form();
}

function process_form()
{
	global $phpcdb, $phpcid;

	if($phpcid)
	{
		$plan = $phpcdb->getPlanTrabajo($phpcid);
		if(empty($plan))
			return "Aún no se ha adjuntado el plan de trabajo";
		//echo tag('embed', attributes('src="adjuntos_evento/'.$plan.'"','width="900px"','height="900px"'));		
		if(strpos($plan, ".pdf")>0 | strpos($plan, ".PDF")>0)
			return '<embed style="margin:0 auto;" type="application/pdf" src="./adjuntos_evento/'.$plan.'" width="900px" height="800px">';
		else
			return '<a href="./adjuntos_evento/'.$plan.'">Click aquí para descargar</a>';
	}	
}

?>
