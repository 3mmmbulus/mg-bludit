<?php

echo Bootstrap::pageTitle(array('title'=>$L->g('About'), 'icon'=>'info-circle'));

echo '
<table class="table table-striped mt-3">
	<tbody>
';

echo '<tr>';
echo '<td>Maigewan Edition</td>';
if (defined('MAIGEWAN_PRO')) {
	echo '<td>PRO - '.$L->g('Thanks for supporting Maigewan').' <span class="bi bi-heart-fill" style="color: #ffc107"></span></td>';
} else {
	echo '<td>Standard - <a target="_blank" href="https://pro.maigewan.com">'.$L->g('Upgrade to Maigewan PRO').'</a></td>';
}
echo '</tr>';

echo '<tr>';
echo '<td>Maigewan Version</td>';
echo '<td>'.MAIGEWAN_VERSION.'</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Maigewan Codename</td>';
echo '<td>'.MAIGEWAN_CODENAME.'</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Maigewan Build Number</td>';
echo '<td>'.MAIGEWAN_BUILD.'</td>';
echo '</tr>';

echo '<tr>';
echo '<td>Disk usage</td>';
echo '<td>'.Filesystem::bytesToHumanFileSize(Filesystem::getSize(PATH_ROOT)).'</td>';
echo '</tr>';

echo '<tr>';
echo '<td><a href="'.HTML_PATH_ADMIN_ROOT.'developers'.'">Maigewan Developers</a></td>';
echo '<td></td>';
echo '</tr>';

echo '
	</tbody>
</table>
';
