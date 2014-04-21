mvc
===

MVC pattern implementation

In index.php you should prepare instance of IRequestHandler and call handle(Request) method.

It's assumed most of the times you will use CleanUrlControllerRoute(). CleanUrlControllerRoute
match URL with pattern "/:controller/:action[/:param1][/:param2]", try to create given
controller and try to call 'postAction', 'getAction' or 'handleAction' method depending on
request method. But you can easily define your own IRequestHandler to work for example
like this:
 new CallbackRoute($urlPattern, $callback);

IRequestHandler::handle() must return instance of IResponse, but in controller's action
which called by CleanUrlControllerRoute you can return several types of result:
 - null     : default template will be created for a given controller-action pair,
 - array    : default template will be created for a given controller-action pair and
              will be filled with returned array of parameters,
 - string   : raw output for a browser,
 - ITemplate: will be served with 200 OK status,
 - IResponse: here you can control every aspect of Response: status code, headers
              and body.
