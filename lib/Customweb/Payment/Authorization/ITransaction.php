<?php
/**
 *  * You are allowed to use this API in your web application.
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
//require_once 'Customweb/Payment/Authorization/ITransactionHistoryItem.php';
//require_once 'Customweb/Payment/Authorization/ITransactionRefund.php';
//require_once 'Customweb/I18n/LocalizableString.php';

/**
 * This interface defines a payment transaction.
 * This object is created as part
 * of the authorization process by the different authorization methods. Each
 * authorization method may return a different implementation of the object.
 *
 * The client of the API is responsible for the persistence of the object. He
 * may store the object as a serialized item in the database.
 *
 * Best pratice is to start a new database transaction before loading the object
 * from the database. Then preforme the requested action (refund, cancellation
 * etc.) and then store the object back to the database and commit the database
 * transaction.
 *
 * @author Thomas Hunziker
 *
 */
interface Customweb_Payment_Authorization_ITransaction {
	const AUTHORIZATION_STATUS_PENDING = 'pending';
	const AUTHORIZATION_STATUS_SUCCESSFUL = 'successful';
	const AUTHORIZATION_STATUS_FAILED = 'failed';

	/**
	 * The id of the transaction.
	 * This id is generated by the shopping cart.
	 *
	 * @return String The transaction id.
	 */
	public function getTransactionId();

	/**
	 * Returns a string which contains a unique transaction id, which is either based on the 
	 * transaction id or / and on the order id.
	 * 
	 * @return string
	 */
	public function getExternalTransactionId();

	/**
	 * The payment id of the transaction in the payment service provider
	 * system.
	 *
	 *
	 * @return mixed The payment id in the PSP system.
	 */
	public function getPaymentId();

	/**
	 *
	 * @return Customweb_Payment_Authorization_ITransactionContext
	 */
	public function getTransactionContext();

	/**
	 * This method returns the alias which is created during the
	 * authroization.
	 * The alias can be used for futher transactions
	 * to prevent the customer from entering the credentials again.
	 *
	 * This field may be used, but it is better to use a separte field,
	 * which is added in the concrete implementation of the interface.
	 *
	 * @deprecated
	 *
	 * @return String Alias
	 */
	public function getAlias();

	/**
	 * This method returns a string which can be displayed to the
	 * customer for the alias created by this transaction.
	 *
	 * If this method returns NULL. No alias is created during this
	 * transaction. The value returned by this method is used later
	 * for other authorizations.
	 *
	 * @return String Alias for Display
	 */
	public function getAliasForDisplay();

	/**
	 * This method returns a list of properties to show in the backend
	 * for the user.
	 *
	 * The structur of the returned array is:
	 * array (
	 * 'key' => array(
	 * 'label' => 'Label of the Item',
	 * 'value' => 'Value of the Item',
	 * 'description => 'The description of the item (optional).',
	 * )
	 * );
	 *
	 * @return array
	 */
	public function getTransactionLabels();

	/**
	 * The amount of the transaction.
	 *
	 * @return decimal The amount of the transaction.
	 */
	public function getAuthorizationAmount();

	/**
	 * The currency of the transaction
	 *
	 * @return String The currency code.
	 */
	public function getCurrencyCode();

	/**
	 * This method returns the payment method of the transaction.
	 *
	 * @return Customweb_Payment_Authorization_IPaymentMethod Payment Method of this transaction.
	 */
	public function getPaymentMethod();

	/**
	 * This method returns the authorization method of this transaction.
	 *
	 *
	 * @return
	 *
	 */
	public function getAuthorizationMethod();

	/**
	 * Returns a list of history items.
	 * Each history item describes a previous
	 * state of the transaction.
	 *
	 * @return Customweb_PaymentAuthorization__ITransactionHistoryItem[] List of history items.
	 */
	public function getHistoryItems();

	/**
	 * This method retruns a list of strings.
	 * Each string contains a error message, which may explain
	 * the current state of the transaction. The last message on the list is the most generic and may
	 * be used for explaining the failing to the user.
	 *
	 * If this method returns an empyt array, there is no error regarding this transaction.
	 *
	 * @return Customweb_Payment_Authorization_IErrorMessage[] List of error messages.
	 */
	public function getErrorMessages();

	/**
	 * This method returns true, when the authorization has failed permanently.
	 *
	 * @return boolean Ture, when the authorization failed.
	 */
	public function isAuthorizationFailed();

	/**
	 * This method returns true, when the authorization is done, but it may be not
	 * certain.
	 *
	 * @return boolean True, if the authorization is not certain.
	 */
	public function isAuthorizationUncertain();

	/**
	 * Returns true, when the transaction is authorized.
	 * May be the authorizaiton
	 * is unceratin, but the authorization was somehow successfull.
	 *
	 * @return boolean Is authorized or not.
	 */
	public function isAuthorized();

	/**
	 * Returns true, when the transaction is captured.
	 *
	 * @return boolean Is captured or not.
	 */
	public function isCaptured();

	/**
	 * Returns true, when the transaction is cancelled.
	 *
	 * @return boolean Is cancelled or not.
	 */
	public function isCancelled();

	/**
	 * This method returns a list of line items with the residual amount to capture.
	 *
	 * @return Customweb_Payment_Authorization_ITransactionCapture[]
	 */
	public function getUncapturedLineItems();

	/**
	 * This method returns the total amount that is captured for this transaction.
	 * This amount
	 * can never be greater than the self::getAuthorizationAmount().
	 *
	 * @return double Total amout that is captured.
	 */
	public function getCapturedAmount();

	/**
	 * This method returns the total amount that is refunded for this transaction.
	 * This amount
	 * can never be greater than the self::getCapturedAmount().
	 *
	 * @return double
	 */
	public function getRefundedTotalAmount();

	/**
	 * This method returns a list of refunds done for this transaction.
	 *
	 * @return Customweb_Payment_Authorization_ITransactionRefund[]
	 */
	public function getRefunds();

	/**
	 * This method returns a list of captures done for this transaction.
	 *
	 * @return Customweb_Payment_Authorization_ITransactionCapture[]
	 */
	public function getCaptures();

	/**
	 * This method returns a list of cancelss done for this transaction.
	 *
	 * @return Customweb_Payment_Authorization_ITransactionCancel[]
	 */
	public function getCancels();

	/**
	 * This method returns true, when a partial refund is possible on this
	 * transaction.
	 * If no more refund is possible. This method has to return
	 * false.
	 *
	 * @return boolean True, when a partial refund is possible on this transaction.
	 */
	public function isPartialRefundPossible();

	/**
	 * This method returns true, when a complete refund is possible on this
	 * transaction.
	 * This method has to respect the inner state of the transaction.
	 *
	 * If the method self::isPartialCapturePossible() returns true, then this
	 * method must also return true. May be a complete refund does only refund
	 * the open amount, which is not refunded actually.
	 *
	 * @return boolean True, when a complete refund is possible.
	 */
	public function isRefundPossible();

	/**
	 * This method returns true, if the transaction is closeable if a partial refund
	 * is executed.
	 * If a refund closes the transaction no further refunds are possible,
	 * even if not the whole captured amount is refunded. If this method returns true,
	 * this implies also that multiple refunds are possible. If the payment
	 * service provider supports only one refund per transaction, then this method
	 * must return always false.
	 *
	 * @return boolean True, if the refund can close the transaction.
	 */
	public function isRefundClosable();

	/**
	 * This method returns the maximal refundable amount on this transaction.
	 *
	 * @return double Maximal refundable amount of this transaction.
	 */
	public function getRefundableAmount();

	/**
	 * This method returns a list of line items with the residual amount to refund.
	 *
	 * @return Customweb_Payment_Authorization_IInvoiceItem[]
	 */
	public function getNonRefundedLineItems();

	/**
	 * This method returns if a partial capturing is possible on this transaction.
	 * The method has to respect the current state of the transaction. This
	 * means if the transaction is captured, this method must return false.
	 *
	 * @return boolean True, if a partial capturing is possible.
	 */
	public function isPartialCapturePossible();

	/**
	 * This method returns true if a capture is possible.
	 * The capture does
	 * capture the whole transaction amount.
	 *
	 * This method has to return true, when the method self::isPartialCapturePossible()
	 * returns true.
	 *
	 * @return boolean True, if a capture is possible on this transaciton.
	 */
	public function isCapturePossible();

	/**
	 * This method returns true, if the transaction is closeable if a partial capture
	 * is executed.
	 * If a capture closes the transaction no further captures are possible,
	 * even if not the whole authorized amount is captured. If this method returns true,
	 * this implies also that multiple captures are possible. If the payment
	 * service provider supports only one capture per transaction, then this method
	 * must return always false.
	 *
	 * @return boolean True, if the capture can close the transaction.
	 */
	public function isCaptureClosable();

	/**
	 * This method returns the maximal capturable amount of this transaciton.
	 *
	 * @return double Maximal capturable amount of this transaction.
	 */
	public function getCapturableAmount();

	/**
	 * This method returns true, if the given transaction can be canceled.
	 *
	 * @return boolean True, when the transaction can be canceled.
	 */
	public function isCancelPossible();

	/**
	 * This method provides customer data, which must be stored by the shop
	 * system.
	 *
	 *
	 * @return Customweb_Payment_Authorization_IPaymentCustomerContext
	 */
	public function getPaymentCustomerContext();

	/**
	 * This method returns true, when the transaction is paid.
	 * In case of credit
	 * card this should return after the authorization always 'true'.
	 * In case of an open invoice this method should return false until the payment
	 * of the customer is received.
	 *
	 * @return True, when a payment is received.
	 */
	public function isPaid();

	/**
	 * This method returns the setting key, which contains the status id to be
	 * set.
	 *
	 *
	 * The shop system will based on the provided setting key set the order status
	 * on every time when the transaction is persisted.
	 *
	 * In case no order status should be set (pending transaction) the method
	 * returns NULL.
	 *
	 * @return string
	 */
	public function getOrderStatusSettingKey();

	/**
	 * This method returns a key / value map which contains important information about
	 * the transaction, which may be used by other systems to work later with the
	 * transaction data in a separate system (ERP / CRM).
	 *
	 * @return array
	 */
	public function getTransactionData();

	/**
	 * Returns the status of the authoriaztion.
	 * It can be either:
	 * - self::AUTHORIZATION_STATUS_PENDING
	 * - self::AUTHORIZATION_STATUS_SUCCESSFUL
	 * - self::AUTHORIZATION_STATUS_FAILED
	 */
	public function getAuthorizationStatus();

	/**
	 * Returns a DateTime on which the update service should update the
	 * transaction.
	 * In case the method returns NULL no update is executed.
	 *
	 * The returned date will be the earliest date when the transaction is updated. I may
	 * be later updated. This depends on the scheduler. In case the transaction
	 * should be updated later again after execution of the update the method most returns
	 * a date again to be updated.
	 *
	 * The update handler sets per default the date to null before the update is executed.
	 *
	 * @return DateTime
	 */
	public function getUpdateExecutionDate();

	/**
	 * This methods returns HTML code with additional information for the customer.
	 * This can contain bank account number, tracking number and so on.
	 *
	 * @return String | NULL
	 */
	public function getPaymentInformation();

	/**
	 * Returns true when the transaction is processed on a live platform.
	 *
	 * @return boolean
	 */
	public function isLiveTransaction();
	
	/**
	 * Returns true when the customer refuses to pay. This is set typically with a late delay (up to 12 months).
	 * 
	 * For example when the customer issues a chargeback this flag may be set to true or when the customer does not pay 
	 * the invoice.
	 * 
	 * @return boolean
	 */
	public function isCustomerRefusingToPay();
	
	/**
	 * Returns true when the transaction is finally decline when the transaction was before 'uncertain'.
	 * 
	 * @return boolean
	 */
	public function isUncertainTransactionFinallyDeclined();
	
	/**
	 * Returns the current version number of the transaction.
	 *
	 * @return int
	 */
	public function getVersionNumber();
	
	
	/**
	 * Returns true, if the shop should send a confirmation email to the customer.
	 * 
	 * @return boolean
	 */
	public function isSendConfirmationMailEnabled();
}