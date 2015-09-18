##Purchasing a Plesk for SIDEKICK license(s) 

If you would like to purchase SIDEKICK for Plesk or arrange a free demo, please visit 
[http://www.sidekick.pro/plesk-extension](http://www.sidekick.pro/plesk-extension) or contact [sales@sidekick.pro](mailto:sales@sidekick.pro) 

##Installation Instructions

###Option 1: Manual Activation

To activate SIDEKICK for Plesk, enter the SIDEKICK API credentials you received when you opened your account within the SIDEKICK extensions page. 

If you have misplaced your API Credentials, please contact us at [support@sidekick.pro](mailto:support@sidekick.pro). 


####Extension Catalog
To install the SIDEKICK for Plesk extension into a single Plesk instance, 

1. Login to Plesk
2. Click “Extensions” and then 
3. Click "Extensions Catalog"
4. Find SIDEKICK and click "Install"

####Download
Download the *sidekick_plesk_extension.zip* from the *dist* folder within this repository and upload it to your Plesk installation. 

1. Login to Plesk, 
2. Click “Extensions” and then
3. Click “Add Extension”
4. Upload the zip file and click OK

###Option 2: Deploy Plesk Automatically with Every Installation

Through some simple bash scripting you can utilize the Plesk API to install and activate 
the SIDEKICK extension.

		/usr/local/psa/bin/extension -i sidekick_extension.zip;
		plesk php /usr/local/psa/admin/plib/modules/sidekick/scripts/configure.php sidekick_api_email sidekick_api_password;
#####Example
		/usr/local/psa/bin/extension -i /mnt/hgfs/dist/sidekick_plesk_extension.zip;
		plesk php /usr/local/psa/admin/plib/modules/sidekick/scripts/configure.php <me@email.com> <password>;


###Option 3: For Advanced Mass Deployments

For advanced installations you can directly call the SIDEKICK API to authenticate 
and generate a new key. 

#####Follow these articles:


1. [API Authentication](https://sidekick.zendesk.com/hc/en-us/articles/208566047-API-Authentication)

2. [Key Generation](https://sidekick.zendesk.com/hc/en-us/articles/207839558-Generate-Activation-Key-via-API)

3. [Plesk Configuration](https://sidekick.zendesk.com/hc/en-us/articles/209973987)