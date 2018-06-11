<?php
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2016 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

require_once 'Customweb/Licensing/BarclaycardCw/Key.php';Customweb_Licensing_BarclaycardCw_Key::decrypt('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');