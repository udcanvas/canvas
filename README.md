# lti
Re-organizing Becky's LTI tools for sharing and testing

I want to set up a new folder structure for my LTI 1.1 tools. The plan is to organize the folders around the dependencies. I had thought that I could use a single include file for all LTI scenarios, but I've decided to back down from that and use separate include files for various scenarios. This makes it easier to debug one scenario without having to retest the others. Each of the folders in this repository targets a different scenario based on whether the tool requires a key:secret, whether it requires an API token, and whether it is going to be shared with other institutions. Sharing tools that require an API key is a dicey proposition, and maybe we shouldn't even be doing it, but I'm putting a folder in for it anyway.

Folder list:

anon - tools that don't even need a key and secret. Mostly editor buttons

keysecret - tools that require ONLY a key and secret

admin - inhouse tools that use an admin token

context - tools that require a stored token. 1 token stored per context, even if the user is an admin

temptoken - tools that request a new token for each launch, and then delete them

