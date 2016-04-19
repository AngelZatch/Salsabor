<?php
/** This file has to be executed very often and will create notifications for the team.
Notifications can target a transaction, a maturity, a mail
A notification will have token which will be read by the application to know what it's about:
-> Type TRA (Transaction), PRD (Product), MAT (Maturity), MAI (Mail)
-> Subtype NE (Near Expiration), NH (Near Hour Limit), E (Expired), L (Late)
-> Target the ID of the transaction, maturity, product, user...
-> Date time of the notification
-> State 0 (read), 1 (new)

exemple: a product is gonna expire in 2 days:
PRD-NE | 1275 | 1 - The product PRODUCT_NAME of USER will expire on PRODCUT_VALIDITY.
PRD-NH | 1275 | 1 - The product PRODUCT_NAME of USER has HOUR remaining.
MAT-L | 10024 | 1 - "The Maturity of the transaction TRANSACTION_ID of user USER, scheduled for MATURITY_DATE, has not been paid yet.
**/


?>
