jetpack-github
==============

This plugin lets you change git branched of Jetpack without leaving WordPress.


**This plugin should not be run on a production environment!**

Don't use this plugin if you are planning to developing from using it. It changes your repos permission settings by running.

		git config core.filemode false
on plugin activation. 
 
Use it only for testing or trying out different branches or tags Jetpack and confirm bugs in the UI.


Setup
---

1. [install VVV](https://github.com/Varying-Vagrant-Vagrants/VVV)
2. Clone the Jetpack git repo in the *wp-content/plugins* folder of your favourite WordPress install. 

		git clone https://github.com/Automattic/jetpack.git
	
3. Clone the Jetpack-github git repo in the *wp-content/plugins* folder as well

		git clone https://github.com/enejb/jetpack-github.git
		
4. Activate Jetpack Plugin, then activate Jetpack Github
5. Go to Jetpack -> Github and change the branch as needed. 
*Every time you Switch branches you will also pull the latest changes from github*