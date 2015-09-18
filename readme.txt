Installation Instructions

To install the SIDEKICK for Plesk extension into a single Plesk instance, login to Plesk, 
click “Extensions” and then “Add Extension”. 

Activation Instructions

To activate SIDEKICK for Plesk, enter the SIDEKICK API credentials you received when you 
opened your account above. 

If you have misplaced your API Credentials, please contact us at support@sidekick.pro. 

Purchasing a Plesk for SIDEKICK license or licenses 

If you would like to purchase SIDEKICK for Plesk or arrange a free demo, please visit 
http://www.sidekick.pro/plesk-extension or contact sales@sidekick.pro 

Deploy Plesk Automatically with Every Installation

Through some simple bash scripting you can utilize the Plesk API to install and activate 
the SIDEKICK extension.

		/usr/local/psa/bin/extension -i sidekick_extension.zip;
		plesk php /usr/local/psa/admin/plib/modules/sidekick/scripts/configure.php sidekick_api_email sidekick_api_password;
Example
		/usr/local/psa/bin/extension -i /mnt/hgfs/dist/sidekick_plesk_extension.zip;
		plesk php /usr/local/psa/admin/plib/modules/sidekick/scripts/configure.php <me@email.com> <password>;


For Advanced Mass Deployments

For advanced installations you can directly call the SIDEKICK API to authenticate 
and generate a new key. 

Follow these articles:

API Authentication - https://sidekick.zendesk.com/hc/en-us/articles/208566047-API-Authentication
Key Generation - https://sidekick.zendesk.com/hc/en-us/articles/207839558-Generate-Activation-Key-via-API
Plesk Configuration - https://sidekick.zendesk.com/hc/en-us/articles/209973987