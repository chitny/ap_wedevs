# Welcome to my technical Test for WeDevs

Author: Ignacio Chitnisky
Email: ignacio@chitnisky.com
Date: November 2022
Version 0.1


## Plugin goal


Create a plugin that displays a form through a shortcode to complete and submit a (job?) application. The form must save the data in a table called applicant_submissions previously created when the plugin is activated for the first time. In turn, you must send a confirmation email when the user submits the form correctly.

In the backend the admin will be able to enter to see all the applications from an option in the menu, he will be able to order them in ascending or descending order (I chose only two places as determinants for the order, the last name and the app time), he will be able to search in any field and delete both individually and in groups the users applications.

Finally the administrator will have access to a widget in the dashboard that will show the last 5 applications sent.

This plugin only has two files, **ap_wedevs.php** and **uninstall.php**

**ap_wedevs.php** - It has all the functions for the operation of the entire plugin.
**uninstall.php** - It will be executed when the plugin is deleted, the file will be in charge of deleting the generated table.
**readme_es.docx** - This document in english


# Functions created for each plugin goal



## When the plugin is activated for the first time:

When the plugin is active for the first time, through the **register_activation_hook** the function **ap_wedevs_create_db** will be executed.

In turn the line of code with the hook **add_action('admin_menu', 'ap_wedevs_add_menu')**; will add the option to have access to an option in the dashboard menu with access to the page where we will see all the job applications.

Finally we use two **require_once**, both for the **Wp List Table class** and for **file.php**. We will use the first one to manage and print on the screen all the applications in the admin page, the second one will be necessary so that the user can upload his file in the form.

To be able to use the **[applicant_form]** shortcode later, use the add_shortcode hook, in the line **add_shortcode('applicant_form', 'register_ap_wedevs_shortcode');**. It will call the function **register_ap_wedevs_shortcode()** every time it finds the required shortcode in a post, page or other.

Finally, the hook to execute and have the widget on the dashboard is **add_action('wp_dashboard_setup', 'wedevs_add_widget');**. This hook calls the **wedevs_add_widget** function that will be in charge of the widget itself.


# Creation of the DB




## function ap_wedevs_create_db

The **ap_wedevs_create_db** function will create a table within the database, named ***applicant_submissions***, without a prefix and with 9 columns, these are:

id, mediumint(9) with autoincrement.
time, datetime DEFAULT '0000-00-00 00:00:00'
firstname, varchar(25)
lastname, varchar(25)
presentddress, varchar(100)
emailaddress, varchar(100)
mobile, varchar(20)
postname, varchar(55)
cv, varchar(255)

Each column will keep the records, one per row, of each submitted application. I chose this design to save the data in the DB since it was the most practical with the time I had to do the all the technical test. Another optimal way was to iterate in an array with only two columns per applicant where in the most important column it would be saving all the data in an array in a single cell.



# Functions responsible for the operation of the frontend


Here are all the functions used to manage the printing of the form on the screen and its operation both in the front and in the backend.


## function print_app_form_wedevs


The **app_form_wedevs** function is the one that shows the form that needs to be completed on the page where the shortcode is placed.
 
In it we start by defining the variables of each field of the form. In line 74 we add an **if ($completed)** whose mission is to clean the form, blank all fields, in case the form has been previously uploaded correctly. At the same time, it will print a message notifying us that the application was sent and if the mail was sent correctly, it will also print a message that the mail was sent correctly. Otherwise, it will also specify it with a message.

From line 103 to 141 we give the style to the form.

In line 144 we have an **if (is_wp_error($wp_error))** where we are asking that in the case of finding an error, print it on the screen.

From line 155 we have everything related to the code to print the form. There is nothing very complicated about this. If I must clarify that all the requirements or limits of each form I control them both in the frontend and in the backend. For example, in the frontend I use the word "**required**" in the code to say that a field is required, but also, later in the function explained below I show how I ask for those same requirements in the backend.


## function registration_validation

***//This function check if all the form fields are ok and continue with the submission***
This function is the one that controls that all the limits or requeriments of the form, which were previously established, are fulfilled. They are: if the required field is complete (we do this using “**empty**”), if the email address entered is correct and if the fields are ok with the character limit. Given the freedom in the task, I decided all this requeriments on my part.



## function complete_registration

***//"complete_registration" If everything is correct, this function add the records to the table***
After verifying that there are no errors in the fields, this function will be executed in order of the **register_ap_wedevs_shortcode** function, after having pressed the **Submit** button. In it we save the file in the “uploads/year/month” folder and save the data in the database using an insert. We use "**insert**" and not "**query**" for several reasons, the main one is to avoid code injection in the DB.


## function deliver_mail($emailaddress)

***//IMPORTANT! this function doesn't work in localhost because wp_mail doesn't work in localhost.***
This is the function that is responsible for sending the mail through **wp_mail**. This function does not work on localhost.


## function register_ap_wedevs_shortcode

***//register the shortcode and check the form for errors and submission***
This is the main function of the frontend and its actions. When called through the shortcode, it calls **print_app_form_wedevs**. In turn, when we press the submit button, it is the one that calls the **registration_validation** function, sanitizes the information of each field and calls the complete_registration function. Finally, when checking that everything is ok, it calls the deliver_mail function.


# Functions and class for the backend


These are the class and all the functions to have access and control of the page in the dashboard where we will see all the applications sent.


## class wedevsListTable extends WP_List_Table

***// Extend class***
To create and display the data in the admin we use and extend the **Wordpress WP_List_Table class** with various functions.


## function get_table_data

***// Get table data***
This function will show us all the data of the applications if the search field is empty. If the search field has any character, I decided to search for that string in all available data fields (firstname, lastname, etc), this is easily modifiable by removing any of the lines from 362 to 368.


## public function get_columns

***// Define tablecolumns***
Print the names of each column and establish what data should be displayed below each column.


## public function delete_entry

***// Delete an entry record.***
We use this function to delete a single entry (from the menu option that will appear below the name)

## function prepare_items

***// Bind table with columns, data and all***
It retrieves from the database, all the information that will be displayed on the administration page.

## public function column_cb

***//Fix the checkbox in every file***
This function is created to be able to have a checkbox at the beginning of each line to be able to select the bulk actions later.

## function get_sortable_columns

***// let me choose what items are selected for the sort***
I decided to just use two columns for sorting as an example, **time and lastname**. If we want to have more parameters to sort the rows, we can add an extra line per parameter here. For example if you wanted to be able to click on firstname and sort the entire table by name, you would add a line similar to **'firstname'=> array('firstname', false),**

## function usort_reorder

***// sort function***
Set a default order (in this case I decided to go by time).

## function column_firstname

***// Adding action links to column***
This is the function that allows us to have what are called action links for each field. In this case I decided that you could only have one action link (**Delete**) and that it only appear below the **First Name** of each row. If you tap Delete, the entire table row in the database will be deleted.

## public function get_bulk_actions

***// To show bulk action dropdown***
It displays the options that we have chosen, in our case only **Delete**. By clicking on the checkboxes and then selecting the **Delete** option, it will call the corresponding function to delete all the selected data from the Database.

## function process_bulk_action

***//As the name says, this function process the bulk action***

## function process_single_action

***//As the name says, this function process the single action***
  

## function ap_wedevs_admin_page

***// Plugin menu callback function***
This function is the one that manages the page in the administrator through the previously created class and 3 functions.

# Functions of the dashboard menu

# function ap_wedevs_add_menu

***//Admin menu setting***
It is responsible for creating the option in the dashboard menu and calling the previously named function.

# Functions responsible for the operation of the widget

## function wedevs_add_widget

***//add Widget***
Add an empty widget with a title to the dashboard and call the following function.

## function wedevs_show_widget

Shows the widget information. To show the last 5 results, I make a call to the database through the line **SELECT * FROM $table_name ORDER BY time DESC LIMIT 5**.

Then I print it to the screen through the code that follows in the echo.


**Author: Ignacio Chitnisky
Email: ignacio@chitnisky.com
Date: November 2022**