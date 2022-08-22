[![CircleCI](https://circleci.com/gh/dcycle/webhook_receiver/tree/1.x.svg?style=svg)](https://circleci.com/gh/dcycle/webhook_receiver/tree/1.x)

Webhook Receiver
=====

Allows Drupal sites to receive webhooks. Webhooks are posts to a specific path on your website used for integration with third party services. The content of the posts is called a "payload".

For example, if you use [Eventbrite](https://www.eventbrite.com) to manage your events, you can set Eventbrite up to inform your site of any changes to events. This is done through webhooks.

No GUI
-----

This module does not contain any graphical user interface. It is meant to be integrated via code to other modules which actually do something useful with the payload.

You can interact with it through Drush and the command line.

Example
-----

On its own, the webhook_receiver does not do anything. It needs extension classes to actually do something when webhooks are called.

Let's look at an example:

Start by enabling webhook_receiver

    drush en webhook_receiver -y

We can now confirm that, for now, no webhooks are defined:

    drush ev 'print_r(webhook_receiver()->webhooks())'

This will give you an empty array. This is because there are no webhooks.

So how to define a webhook?
-----

This project ships with a very simple module, webhook_receiver_example, which defines a webhook as a plugin. Feel free to copy-paste code from that module to define your own webhooks.

webhook_receiver_example requires that webhook payloads include a specific key, 'This must be set!' and that it be a non-empty string. If it validates, it will log itself to the watchdog.

Let's try it:

    drush en webhook_receiver_example -y

Now get the webhooks:

    drush ev 'webhook_receiver()->webhooks()'

    Array
    (
      [webhook_receiver_plugin_log_payload] => Array
        (
          [plugin_id] => webhook_receiver_example_log_payload
          [token] => abc123
          [webhook_path] => /webhook_receiver_example_log_payload/abc123
        )
    )

In your case, a unique token will appear instead of abc123. That is meant to secure your webhook and make sure only authorized sources can access it.

Calling the webhook
-----

When you call the webhook, you will get feedback on what happened. Let's say you have this module on the site http://example.com, you can run:

    curl -I http://example.com/webhook-receiver/webhook_receiver_example_log_payload/abc123

This will give you an error:

    HTTP/1.1 405 Method Not Allowed
    ...

This is normal because webhook receiver only accepts POSTs.

### If the key 'This must be set!' is present in the payload, the value of that key is logged to the watchdog.

    curl -i -X POST \
    http://0.0.0.0:51568/webhook-receiver/webhook_receiver_example_log_payload/6g_FDw46k4A9lFeQheWAYxQRMmAqmtpxvy4uhS-6ICE \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -d '{
    "This must be set!": "It works!!!"
    }'

The response gives some debugging information, and returns a 200 code.

And our webhook does what it's meant to do (as this is just an example): it logs the message "The payload's 'This must be set!' key is: It works!!!" to the watchdog.

### As with any webhook, if the token is incorrect, we get an error.

    curl -i -X POST \
    http://0.0.0.0:51568/webhook-receiver/webhook_receiver_example_log_payload/THE_WRONG_TOKEN \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -d '{
    "This must be set!": "It works!!!"
    }'
    ...
    HTTP/1.1 403 Forbidden
    ...

### As with any webhook, if the payload contains data but is not valid JSON, we get a 400 (bad request) code.

    curl -i -X POST \
    http://0.0.0.0:51568/webhook-receiver/webhook_receiver_example_log_payload/6g_FDw46k4A9lFeQheWAYxQRMmAqmtpxvy4uhS-6ICE \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -d '{"This is bad JSON",}'
    ...
    HTTP/1.1 400 Bad Request
    ...

### If the payload does not contain the required "This must be set!" key, we also end up with a bad request.

    curl -i -X POST \
    http://0.0.0.0:51568/webhook-receiver/webhook_receiver_example_log_payload/6g_FDw46k4A9lFeQheWAYxQRMmAqmtpxvy4uhS-6ICE \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -d '{"The required key is not present": "hello"}'
    ...
    HTTP/1.1 400 Bad Request
    ...

### Our example module simulates what happens if an internal error occurs.

    curl -i -X POST \
    http://0.0.0.0:51568/webhook-receiver/webhook_receiver_example_log_payload/6g_FDw46k4A9lFeQheWAYxQRMmAqmtpxvy4uhS-6ICE \
    -H 'Accept: application/json' \
    -H 'Content-Type: application/json' \
    -d '{
    "This must be set!": "Simulate internal exception on validate."
    }'
    ...
    HTTP/1.1 500 Internal Server Error
    ...
    {"code":500,"time":1661192152,"log":{"errors":[{"message":"An error occurred during processing. Find the following id in the Drupal logs for details","id":"2a9579e8-1e3a-495b-adc8-f0706769a81f"}],"debug":[]},"access":true,"continue":true}%

This allows you to search for the error id which will be in the watchdog.

Local development and testing
-----

request-response-test

drush ev 'webhook_receiver()->requestResponseTest("webhook_receiver_example")'



Similar modules
-----

* [Webhooks](https://www.drupal.org/project/webhooks). Webhooks acts as both a Webhook dispatcher and a Webhook receiver; our module, Webhook Receiver, focuses only on receiving webhooks.
