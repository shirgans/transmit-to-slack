# transmit-to-slack
Send WooCommerce orders to Slack

Send WooCommerce Orders to Slack
======

» **Getting Started**

Step 1: Create Slack App
------------------------
- Go to Slack APIs: https://api.slack.com/apps/ and login
- Create a new app
- Give it a name (WC Orders, for example)
- Choose the workspace you would like to integrate with
- Under “Building Apps for Slack” click on “Incoming Webhooks”
- At the top - Activate Incoming Webhooks
- Then, at the bottom, click on the button to add new webhook
- Choose the channel you wish the orders will show in
- You can create a new channel on slack and then refresh that page, so you can select the channel you just created
- Click Install
- You will see a new webhook URL, something that starts like this: https://hooks.slack.com/services/T8KD1LQ...... 
- Copy this URL

Step 2: Add code
----------------
- Add the following code to your functions.php.

`add_filter('transmit_to_slack_webhook_url', 'slack_webhook_url');`

`function slack_webhook_url(){`

`return 'paste Webhook URL here';`
   
`}`

- change "paste Webhook URL here" to the copied URL (from step 1)

Contribute on GitHub
--------------------
You are welcome to contribute this open source project on GitHub:
https://github.com/shirgans/transmit-to-slack
