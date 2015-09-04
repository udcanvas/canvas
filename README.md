# lti
Re-organizing Becky's LTI tools for sharing and testing

Trying to create a branch for apps and another for tron. Not sure how this is done. I can't seem to clone into my local account, so I'm hoping that making this change will help.

Many of these tools operate under two scenarios, inhouse, and shared. The inhouse scenario uses a single admin token owned by udcanvas. The shared scenario requests a token for each course in which the tool is installed, and stored it in the tokens table of the lti database. In each tool folder, there should be three files, index.php, shared.php and common.php. The common file is included by both shared and index, and contains the bulk of the code. Merging the two existing shared and index files is one of the major steps involved in updating any existing tool. I use Beyond Compare for this, but there is probably some way to do it with Git as well.

