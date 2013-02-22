# Socket

Socket server and client for php.

I can't remember why this exists actually, I was messing with sockets in PHP one night and the end result is this
library, but I can't remember what problem I was trying to solve, but the code works and so I thought I would
push it out since it isn't doing anyone any good just sitting on my computer.

## Run the demo

To get it working, you just need to start the server in one shell, and then run the client in another server

### 1 - Start the server

    php server.php

### 2 - Send the server a message using the client

    php client.php "I am sending a message"

Basically, the Socket server takes a callback and listens for input, when it gets input
it will then run that callback on the input and return it. Take a look at the `client.php` and
`server.php` scripts to see how everything is set up.

## License

MIT, the license of the Gods.
