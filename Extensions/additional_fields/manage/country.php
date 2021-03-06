<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_ADD_FIELDS_COUNTRY';
$path_to_root = "../../..";
include($path_to_root . "/includes/session.inc");

include_once($path_to_root . "/modules/additional_fields/includes/addfields_db.inc");

add_access_extensions();

page(_($help_context = "Manage Countries"));

include($path_to_root . "/includes/ui.inc");

simple_page_mode(true);

if ($Mode=='ADD_ITEM' || $Mode=='UPDATE_ITEM') 
{

	$input_error = 0;

	if (strlen($_POST['description']) == 0) 
	{
		$input_error = 1;
		display_error(_("Country name cannot be empty."));
		set_focus('description');
	}

	if ($input_error != 1)
	{
    	if ($selected_id != -1) 
    	{
    		update_country($selected_id, $_POST['description'], $_POST['codigo']);
			$note = _('Selected Country has been updated');
    	} 
    	else 
    	{
    		add_country($_POST['description']);
			$note = _('New Country has been added');
    	}
    
		display_notification($note);    	
		$Mode = 'RESET';
	}
} 

if ($Mode == 'Delete')
{

	$cancel_delete = 0;

	if (key_in_foreign_table($selected_id, 'cust_branch', 'area'))
	{
		$cancel_delete = 1;
		display_error(_("Cannot delete this Country because customer branches have been created using this Country."));
	} 
	if ($cancel_delete == 0) 
	{
		delete_country($selected_id);

		display_notification(_('Selected Country has been deleted'));
	}
	$Mode = 'RESET';
} 

if ($Mode == 'RESET')
{
	$selected_id = -1;
	$sav = get_post('show_inactive');
	unset($_POST);
	$_POST['show_inactive'] = $sav;
}

//-------------------------------------------------------------------------------------------------

$result = get_countrys(check_value('show_inactive'));

start_form();
start_table(TABLESTYLE, "width='30%'");

$th = array(_("Country"), _("Country Code"), "", "");
inactive_control_column($th);

table_header($th);
$k = 0; 

while ($myrow = db_fetch($result)) 
{
	
	alt_table_row_color($k);
		
	label_cell($myrow["description"]);
	label_cell($myrow["codigo"]);
	inactive_control_cell($myrow["country_code"], $myrow["inactive"], 'addfields_country', 'country_code');

 	edit_button_cell("Edit".$myrow["country_code"], _("Edit"));
 	delete_button_cell("Delete".$myrow["country_code"], _("Delete"));
	end_row();
}
	
inactive_control_row($th);
end_table();
echo '<br>';

//-------------------------------------------------------------------------------------------------

start_table(TABLESTYLE2);

if ($selected_id != -1) 
{
 	if ($Mode == 'Edit') {
		//editing an existing area
		$myrow = get_country($selected_id);

		$_POST['description']  = $myrow["description"];
		$_POST['codigo']  = $myrow["codigo"];
	}
	hidden("selected_id", $selected_id);
} 

text_row_ex(_("Country:"), 'description', 30); 
text_row_ex(_("Country Code:"), 'codigo', 5);

end_table(1);

submit_add_or_update_center($selected_id == -1, '', 'both');

end_form();

end_page();
