<?php

# What about patterns("api/ticket.php:TicketController", ...) since if the
# class is given as the prefix, it isn't defined in more than one file. This
# would allow for speficying imports only if an item is defined in a
# different class (with the array("class", "method") syntax)
return patterns("api.ticket.php:TicketController",
    url_post("^/tickets\.(?P<format>xml|json)$", "create")
);

?>
