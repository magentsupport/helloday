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

//require_once 'Customweb/Payment/Authorization/AbstractPaymentMethodWrapper.php';


/**
 * 
 * @author Thomas Hunziker
 * @Method()
 */
class Customweb_Barclaycard_Method_DefaultMethod extends Customweb_Payment_Authorization_AbstractPaymentMethodWrapper {

	/**
	 * This map contains all supported payment methods.
	 *        	 				  				 	
	 * @var array
	 */
	protected static $paymentMapping = array(
		'creditcard' => array(
			'machine_name' => 'CreditCard',
 			'method_name' => 'Credit Card',
 			'parameters' => array(
				'pm' => 'CreditCard',
 				'brand' => '',
 			),
 			'not_supported_features' => array(
				0 => 'ServerAuthorization',
 				1 => 'Moto',
 				2 => 'ExternalCheckout',
 			),
 			'image_color' => 'data:image/png;base64,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',
 			'image_grey' => 'data:image/png;base64,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',
 		),
 		'americanexpress' => array(
			'machine_name' => 'AmericanExpress',
 			'method_name' => 'American Express',
 			'parameters' => array(
				'pm' => 'CreditCard',
 				'brand' => 'American Express',
 			),
 			'not_supported_features' => array(
				0 => 'ServerAuthorization',
 				1 => 'ExternalCheckout',
 			),
 			'credit_card_information' => array(
				'issuer_identification_number_prefixes' => array(
					0 => '34',
 					1 => '37',
 				),
 				'lengths' => array(
					0 => '14',
 					1 => '15',
 				),
 				'validators' => array(
					0 => 'LuhnAlgorithm',
 				),
 				'name' => 'American Express',
 				'cvv_length' => '4',
 				'cvv_required' => 'true',
 			),
 			'image_color' => 'data:image/png;base64,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',
 			'image_grey' => 'data:image/png;base64,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',
 		),
 		'jcb' => array(
			'machine_name' => 'Jcb',
 			'method_name' => 'JCB',
 			'parameters' => array(
				'pm' => 'CreditCard',
 				'brand' => 'JCB',
 			),
 			'not_supported_features' => array(
				0 => 'AliasManager',
 				1 => 'ServerAuthorization',
 				2 => 'ExternalCheckout',
 			),
 			'credit_card_information' => array(
				'issuer_identification_number_prefixes' => array(
					0 => '3528',
 					1 => '3529',
 					2 => '353',
 					3 => '354',
 					4 => '355',
 					5 => '356',
 					6 => '357',
 					7 => '358',
 				),
 				'lengths' => array(
					0 => '16',
 				),
 				'validators' => array(
					0 => 'LuhnAlgorithm',
 				),
 				'name' => 'JCB',
 				'cvv_length' => '3',
 				'cvv_required' => 'true',
 			),
 			'image_color' => 'data:image/png;base64,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',
 			'image_grey' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEEAAAAyEAAAAAB2ujW1AAAEQUlEQVR42mP4P+CAYdQJ2Jxwt2Dl23k98wUWVC6csmj94hdLWZblLO9YsXjlvlcHkdXd0Vv7ZVHuoj2LDi3WX1y/RHpJ9pIbSyYu1V1a+1IEWd3NnTu+bDqwxW7ri+2pO/l3Oewx3/tvf9xBtUNhP5yxOOGmffpv85VWXjaNdooOS53uuMa7b/EM8b7hxx/gGnTyKhdM3TWN1PVOx50tXdRcHrlauH5xe+Ae7P7J448nixefV/KlUpi6y40lJ+NMEj+nvMoQyrbKSyzsLEmuUKr2rCtsnNEy4cNlDCfsmWAra1pE2AkH77vscFIn7IStfeFPo3lJcML9dquZxtaEnXB1t/MZJybCTjjzJ5SLRCekcxlsJ8YJKQaOYoSd8Nc9K4VEJzydry9OjBNu3HTgIcYJZyQCH5PohB1OxDlhvQdxTlghQ7ITFjwlzgkLWIhzwqQQkp0w7dmoE0adMOoEypwQ9Tw3rril+FrJ3YRP/rdo4oRG6xddLz695H+57OXhw3cQTogXWyJ4rfTnH9RK/l3Fme2rJkb9gTjhU/Iri1ctry6+zni99Y3+m+o3J97Gv13zlOdc5pzlJDihSx5hweWLsNJxXxG+5kf5XOTKGjvY+48iJ6D4u2h+TvqvmGkxL9I+td3d+undUXQnvP/WEtn2vPNg99VNMn8nw0T/afW4UMUJNyuCYlGTo/efDum0DchOeHURkRaOuCP0zuelghP+esZqE241ITthvxKVQ+EcBzENN0RELC7+yQoTPbiSKmlhVSMxTsAEdxWWy1KYI2BguQl5Tvj//4nUsi9UccLxF8Q4AZEWchYc7EPo3l5EpBOWHEdoOq+I6oSff0IWkpYc808g6T7dnIvhhKPqnuWoTvC89PYdQtPSAvRMeTrU4wO6Ewp1E9bjckLtLWTdE+Ow9iNOlrZdiZhhddvKy35z4Yc7WgiZT/JxHJhF032/Dp6AHJAT/LfkNy3MvmuLu2iaFfbwGkLnFyYcXRlc4H14+R1YNbVb6epuSgvof1rLxbA44dyeQ1/f5GAqf/R9kVzYedSaslx4T8RHNUy1j+0O1qNGBCb4suzy7RlX8OQIB6boulyPlvttEu0mTfPyy0PscFfW4SVF2h083frdjd0XKwzSJXw/j7aaRp0w6oRB6oQbmjRzwl0Z4pzwRoQ4J/ySINEJEbG/cohxQvRskHmEnTDDC23Qj7ATpk0Hj0MQdMKck8Q54ZQwiU6I7nqzkhgnxP75Uk2ME6YG/d5HkhPC7K49g47G4HVCiP2NAog6/E7oynvjijH6is8JBTH3EmHK8Tmh7PYzQZg6fE5YcOCTDpYB4P18tRfqORtUG780C0Bqyk7mLvnudwtnXjVBVn7ErYO9k69zVefZrqyu/K5SSE3Zs2uJ9PV2ZHW7D01unVoz3Xlm+6zncyfNj1oYuHjTUqHlnHt+vogZHYkfpE4AAH4T2J6tZ6Y7AAAAAElFTkSuQmCC',
 		),
 		'mastercard' => array(
			'machine_name' => 'MasterCard',
 			'method_name' => 'MasterCard',
 			'parameters' => array(
				'pm' => 'CreditCard',
 				'brand' => 'MasterCard',
 			),
 			'not_supported_features' => array(
				0 => 'ServerAuthorization',
 				1 => 'ExternalCheckout',
 			),
 			'credit_card_information' => array(
				'issuer_identification_number_prefixes' => array(
					0 => '2221',
 					1 => '2222',
 					2 => '2223',
 					3 => '2224',
 					4 => '2225',
 					5 => '2226',
 					6 => '2227',
 					7 => '2228',
 					8 => '2229',
 					9 => '223',
 					10 => '224',
 					11 => '225',
 					12 => '226',
 					13 => '227',
 					14 => '228',
 					15 => '229',
 					16 => '23',
 					17 => '24',
 					18 => '25',
 					19 => '26',
 					20 => '270',
 					21 => '271',
 					22 => '2720',
 					23 => '51',
 					24 => '52',
 					25 => '53',
 					26 => '54',
 					27 => '55',
 				),
 				'lengths' => array(
					0 => '16',
 				),
 				'validators' => array(
					0 => 'LuhnAlgorithm',
 				),
 				'name' => 'MasterCard',
 				'cvv_length' => '3',
 				'cvv_required' => 'true',
 			),
 			'image_color' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAD0AAAAyCAYAAADvNNM8AAAEu0lEQVR42u2ZP2gbVxjAn2rHURObKo1llHsX0ODBgymCmJLBg8BDhiSEDm0pLog20AymZDDBBA8umKDBUBdM4ySOdEOHDBlC0ODBg4cMGjx4yNDBgwdDTW3Hok2qO0WWXr5P/hTOsmyf7j5JIb0PPqTTO717v/f9ue+9J4Qvvvjiiy//U3klpL4t9NFtoU1tCzm/I6QBn7NbQk7+LS5e2RLhbif9KCMaNA09njf0CSutz5iGNEDnTUObMtMyYT3W+9sKmhPR0I7QJwBuFVSdoCZoBiZjpF5feUNetlL6MwAzQdUJ+hInIWdEQy2DVUJ07ghtDKy66QD2kIL1s6Ax7Ms0IlGAWHIAekittMzlU3IcvaPp1oWBL7mBPaABWbK+6nlupmTBDXCNZt8Y0UhTgMG6AzDgNc/AoPkvu5VKCLV354wCcMUAvlEwtIFmWJgF+L9YTwW4qnt3z3JAg2qbbBbHGGZxadB/9PMHgKv6NnmOCVxmWWIcBnuLA3in64IqfdNRF1r9GFDWgwgPOGR2T8D4fnWbpQ/F8aWe+sBVN584wwKNWf3fh1qvh+RVeQ97t3KnpsrffXIstPqBz9pY2HhwbW2FA/p1/7njgUmL059xxfa6h9LSOzCqFf/UEXTp9mkuaPXG2C+AGrQy1tI80KWvOxxBY0LjgsZqzUUSk5NMWbvsCJjUut/GuN4S+hwH9O7ZSKkR6MJsmMvaf7jI3JXlYeuhf+3lgU5pi26gp9sBbc31MRUp0nCzwBhjSWQBqcrfB5xDP7rQvsoMF/xc2bt4/ZQj4PJPHWzZ20zpow1Dr4toEAb8uhUlKHcpClp0XYrCgJ9wQOfCYWdJbOY8F/SyhzJUj7O5+LWuk107xVSYpLVvPS4ttcVmrqXfr6nvhbhWWaue19O74uIgDLrIAV4YCdavuX/uYrMyvJ+HWXZPtoR2kwP61ek6Gwm4gcBUeoJOsu6TQXzPsBQroT5VHg28B25r2ekwvqdYsvnnfeW90VNvC7+FyyxxbMhZ9YvobNreN1RqN2DgOY/gy7sj+hf5tMx43RqCGL7ZwiOdSm1uNgi7Bv9L2PuyHsurmHEbLT7Quk3b5D9O/hJaL0JAvD87qnqD9nUIiwX4fhW3k4/qCw/uzLS2AEBrR4CauHJCy+YXpP7BnF7i7imsw/uxqMFXHXqEq73238PdeGKBE4GfLT2s88WbYOwNt+A5mC+ucHSEroWHY1HQ6pHJoDgYnzrdd5musW2IfkO5DYo7GXFbHwO271GamCHb/2O2ZwRtY4jUuR/b8cB+isA9S7zyihZiBXQT9AXon6AvqX0CdIOuTfoNVzlPCDRCn2uguJuBSz4sJJbof4M0WPw+TXD4nKegMwSGz8/S74ma+2PU/sLWzgK9QrOOIEnQ7sqSe3/GN2igA5W32b6MEdSwrQ/D5upFAsfFwSRBJKk9SddVmaNrfH7GBp08pp0Fujpgu/ssEzRaYJxcWNlcd5CsHyNdIXcPktVv0KToNf3eoskIUf/j9IyhGksnbKGzWtPuWWLkwrWJYtYWVxmamAzN+BxNyoItbg0avE7Ai2Tt/pp+g+S2WZqAELk5es483We/v9q+bGv3xRdffPHlo5N3CwbjLLTGqs0AAAAASUVORK5CYII=',
 			'image_grey' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAD0AAAAyEAAAAACaz1CjAAAEV0lEQVR42u3Yf2gbVRwA8AjdaCfI0FSCpNI/wggjlvyRyQ2C3krAdEanc7Wdy9rDDreaMjbs1lO6eY6sDTSUrNZRtWhS1/24ti6stLSha7RBiz1G0sYuhbpdbaCBZnLY3jzZWc88j5Kl6eXdVUHF3hcC7+VdPrwffN97UQn/2KPapDfpfwV9r2CU7S5qf6QV/zhwZf5WEVeV3WbFsdg78+xk8wR76/PptrnCZeov0izZU+hgy3yZse/g+3xEn2710/PfaPq2khWZMfz0dNuDlg3RKyX97jf8a9l0nDx5p1MQ7r/wdclaNB3XhRluxaGQZsn3SqVZMV68dEnXMy4Ni3FzB3dfAR131FTD4DKfo8DZfGHk2lcwvH90KSCTZkk58NFXnc0g2nfBaLLihm29nquy5xg+1GW+Q3kiDKKzBY7f3JE951n0YDkcfsl8lkvTTbWXQ3B8ug1Cc1W5VvVq1L6ZhkG0LcLp68KvyZx0TyEcthmIZCZ9fkpOvyebc9LHA3AaeyoTBvFJKZweeDIHfa8ADqeSiTGbbtXDabKCiUjSo6wc+iyWTTfVyqFnOEn6yryM1c1kwyC6P1I62xn0xTE4vf/R9Wnf23D6u+ck6VZ847R3EE6PbZGku05vnP7iIpyeYCXpfjec3ouc+3A9+upjSjNaBh3Ry1nh7+7Nhl035KzwH0sl6QfP7N+lPI3KTaU9X2am0jXZzOWB06+fz6Y/+x5OB2/nTKRTe+QMOT65drjhBwayYt4H2TTPbFO2W4P4FIXDgQHofj03bDsDx9/RpWH3HTl9TvbJOCAN58Ppl4tXDwtNtXJS6O0+mcfCzstw/LWaD34DsPIECjkMdxfB8XKX85WuajgcOfX7YUVXgPHW8sdz0w1vLRwNXYUdi+42bODiw5Jdp/cdXJ+tqR7ZKbZaaAkMSKWQyCmp47+M697PnpGd55IP5zjs2wtPTBxaKXm41WIv9cPg8TTat3Vsy92GXw7/LZdcrmph99SeuWGWlG7DU0uBxd6lQO5L3n/1ap9IhEJK3/F6h4agNMPEYjTNpQ5w0SjP/3n5izPM+HhqQHmKYhhB8His1mAQtIjFwCdNJxIUBb4Ph8EbHAd+IZFYree42VmC8HqhdDCoVptMGo3ZrNcbDKm9zKXVGgz5+YKAopWVVmsiYbXqdBiWTNrtFotWG40ShFbb2BiLaTQHDtTXU5RajSAajdcr1ofDarXZDMoyaJOJ5ysrcXx5ubiYprXaWOrZvl0Q2tstFjDUwSCGgWHPy7PbjUankyBwXBBwnCDA+3V1BMHzNhugQX26LIMGPywOEIrSNIK43R6PSgWGNxo1GMKpx2RiGI7T6fz+UCgeF9t2dBiNDEPTbjeCUJTYa1Dv8RiNYhlKh8Mu1+qyOHECzJbNhmE2G8/X1aHokSNgdjEMQeJxv99qtdtnZ8W2HNfYiCAdHQxTX2+xHDs2NCTWgzKKgvLm/2ab9P+L/gM0nBMV21FYiQAAAABJRU5ErkJggg==',
 		),
 		'visa' => array(
			'machine_name' => 'Visa',
 			'method_name' => 'Visa',
 			'parameters' => array(
				'pm' => 'CreditCard',
 				'brand' => 'VISA',
 			),
 			'not_supported_features' => array(
				0 => 'ServerAuthorization',
 				1 => 'ExternalCheckout',
 			),
 			'credit_card_information' => array(
				'issuer_identification_number_prefixes' => array(
					0 => '4',
 				),
 				'lengths' => array(
					0 => '13',
 					1 => '16',
 				),
 				'validators' => array(
					0 => 'LuhnAlgorithm',
 				),
 				'name' => 'Visa',
 				'cvv_length' => '3',
 				'cvv_required' => 'true',
 			),
 			'image_color' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAHgAAAAnCAYAAADEvIzwAAAK9klEQVR42sXcXUwTWx4AcNS9Go17ozEkLmvNOOdMaW2BllKgtkC1QPnIpjcbmtW7Fy+igB/AxYry4S1WFKx8FEFArCh40SUh8WETkk2WN9946xsP+8Abj33s49k9pyhL6cycM9PB2+SfGJNpmfnNOed/zv/MZJlM/uMc13RCMiz0yMoKHczS4HPGcfcoZ+k6QYssv/9Q6pGhg5wldEIy3Nuh5u8844gc5V3DeugaboTOcERwPV8GzuefgXPkM3CNfIau0X/BspEhoXzsr0L5hBHWTh3J0uTz3wO2v7w+xrkXTuzED+lB/Roe/m2DB5cRD0lc2Q7hRxx/x/ET4vUkGrcj9yqOn3E0Id5A4tp2GJtxXF82mW4fz+SUQH7nJijoRKDgFxxdCFhI3MURQMBK4l4yyI2w+zjOer8C2HoQKOrF0YejHwE7iYfbUfwrjiAiWCx/h832+jvoGPJC59MYvDCMoJPEMxxhBF3Pv8QIwrA4RnGMIVhOYhxHBMGKyDqsmGgk36PmOugvRWv1la/jQmUUCVVvvsQ8EqpJvMXxDgnehWTk1X88Sf3CZCsWLpdw8McIBk6oBCaxlWNrPabmpKC5A2BgRAUuDET3HivYeoMMwHGWViM4nrRBx1MELwx9CVXAOCbw/09lK74OnrkGofI12g46sKF60ab0Nw5whsschg1j4IRCYMQZWyLqWm9HlAnYHtClHWvr2aQB8/aBabnfN5SGOOAY3ISOJ0grYHLDKLkGZ+tnTwqVc0gJMPS+78mg0/QfgvCqD+NusQLzxhsofYyk3LWw4wgGRjRgaL0XS+9OW79L4lKAYdGAT+r3BXvIAksHMSwJbYBB+eSq4pvcMzOoFFjwLq5rMOD7D53T/7TICsyZmk4rAs7r9LEA89aAZ++xnKWXYwHWOR/liOKWPjHC0sdIa2ChfLxNyTVIjvueV0gFsOKeQjoh019dYwHmz7d5FAHnd2wwACfEegZY1ONjAXa7Q39Izz1ChzFufD+AgXvSrOgaVM751AJzdQunNQHmhCYLE7CphXlcAObbOpDfjmjAuPUGRI+3P1ikAcOS4IboRS0JTW/jKgZeB86xTTngHDzFUdY9z22qBdbXLHo0ASbjHQswPN/CPC7wee0RFuDk/FfswtgexGnAfPGvPenj/tQRWPIIMQLHwIUhM2nxYl0r5x45LVSMVWHgf24DT8SVXFehcsZIcNUCC7ULwSytPhj4Mw0YnG9BJBunT89Ch0EewaUAW+9+Ejs+xxY6hoERtYsuCjrTLmrxQD0j8L+VjHFkrp1b9iJfEbBndjkjYO/ipobATc0swGfzblEn4LzlVj0TcH636HjG2e9bWIDPunpPinbPDMDGsqE/Ze3jx+SeOQ49sygj4JpFpHZRRWRK8zNgAeZMLRb64sadDQZgybsTFHa3sQCLtUCMu84CbHKHju8nsP7Sq3Y5YH3Vm7i+KhqgAYPqdzqN/qTQQRZgcL5Vdpqgs7bngLw7iAYMrV0N0sA9a3Tg/k/iCRbGZQAWHOG2fdMNhQ7CSzMJWeDK+XZj9byZBgy9733addOGplUqsLH1k2zWmNc+yAIslth8XXEDhQRXHpgv6m+WAF5nTbJ417Pm/fAFF186MTCSA4a177JJ0YEKXLM4rSHwtStUYFNLQup4MifdxpUHhta7Ycnu3dqXzQKM/22WnCKxZ9EIOJ+twbLhbC2B4aXpdQrwzvROXzUflwMWat5vaXfngWs6ehfdgqQKD9DU7mUBxkmU5AQ+WUFiAN5bedo5h6KQWQnwzjzYORIh2Xum1zC36mVOElcGmCx+7GTaVfOLFGB0xr9yVCvjAyzAvKFFL55c3YrRgGFBl+xcWrB1BxmA43KVI9yKYyoXOnCMBsRWx5hb78WXERqwbddiCax+00AD5uve67Uch5dpwPB8W0N619qRDcy3EQ2Yy/+lRLYXKby/SQPm7f2y4xKwP9WpBx5BoGx0S3CPWBTj1k4dwd0zogCnFCsMNQscDVioWbqi3fiRe81HbcHG1rQLLOTdDDIAx+V2XyQrSIX3EQ0YFj2kZpa847FHg7VoRSVS6J5qoAHnVr1y7r0pqMDVS8uaAf/ZcPUUDRhPlVIHfr//EDDfSjAAt8m3vICOBVhnFa8gpX1f6aAz42KDa2yaTHsYs+ctGnCWfyWtsIKBt+Rb8G8JTdN8jJugACPS2nYSo7xbFRgY0YBpW3+gpdvHAqxkjITW4WyMHMuomuQap05VgPuVGV4kuNLA0PNa9HtwJh2mAONp1YfvtcumDc1RGrAutz3n/8nVzXUqcH7XIvV3Cx9EacDQ3r+hZqMb73h6BQMn1JYLDe4JjtJ6V2nAvCcqmiwJ3vl6GrBQ99GiGTBnuOalAX+tDRsMd04B801EA+YLAno6cHecBszb+lVvZUkW3y88DaiqB1dEYpJbclyzJ3H2jOSA9ZVzcaniRm7V2xw68JJ2K29kyywNWDC1JktZGDrAAEytipC5NQZG1C66qM+Z6fmRyhDGHVJa8CcFBNFhwDMZoAJXzQWkN9asHKIDf1jTeBxujssBQ1NbjGTEwHQzQQPmLR311NZb1G1mATYU953S7Bxdw3rgfLbFCkz2R4vhwItTCRow556R3Z0heN9uyALXLmm3hedLNx2RAwamNjJdqsfAiAbMkhSBgu42FmBNTzLZvT47iYETTFt2yiK+9IWNF14MjGjAasuFu4GZ9korAK6gAW+HPLBQ0Mm0KwEU3ltlAF7N2ocPdIUDbHuyxhrT576TsW8FnFv7wanZSZMpjRbAZIWLZYl0e/M7DbhvX0p8OOFqYAEGZRMpFxiUj+swMPpWwLB2qUfbcdjYvJUhMFNiQMZVNuB+s1QtO5PnqIAzvMYCvHeqhGGj3xJYqPttXVPgc7nNgxkB53UyPX4BirqcLMBS1R7O3m8BxQNx3jFwRelODcEZbmNMshK7V7SSmTjB/ZbAJNESWQnLYMHjhi0DYFLxYUqIBGsgSAW298Wlb5BgOwZGX3dzgJLQGix53MgXP9LnOp//MTXJCx2EJaHveceQBziHP7NOk0DZ+GBK662YbPw9gLm6ldOaAZ9x+I+qBYbmzkb25cR7G3TgnqgkcElwbTewqnowbSXLM30qpSTpfhH/PYAN9R+9Go/D1zfVAEsV5EUrSF8eH5UDJk86SC1DkkdI9xOYLx9P2doDXJM2DIyowJWzYb7yVTMJmBLRZlg9nx7e+R4asL52Kaw1cI9y4PYo8zBAKkgMwMD+UCe5xWcfgUH52OreuTeomFxjAI6rmbML1YsJOWCh9sOmpsCC+YZRMbD5NvNWT2i562MB3l29SrkB7QOe/QIGrrFPe0uF5NlgWEFwacCzqqY0QvXCMgUYmfwrhzWcD/sPKwGGeXdiihK5wkCUBgyLejekx9+Bwf0AFspHRefcQsVEkAWYtjQpecN7FxppwIaaZU7jdenrMVZgPv+OogemgDUQpwEL9l7J1TBYHIxpCQxco58494To81IkG08+4U8H3lDdY9a/MdKAYd1Hn6bA5wzX2xmBE0oeEk9WkHa9o0O6i5avIAHHI0geRMOw/1EDDJzhTd41Qp1DC2Wj9SzA/KUZ1XuozjhWjlKB6/8R1RSY3Lmpb+ARf0uO8vd4kLfoiH1X6tt0lKxSkQ31uc5HOWS7jlAaasLA/dDxOIKBcQxFgPPJIJ4DN8MLYZ+h9Bmn5O05Ocm340ycoAXrFh/JOsAP4m/e+Rom98rx/wFJDAwzelpxzAAAAABJRU5ErkJggg==',
 			'image_grey' => 'data:image/png;base64,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',
 		),
 		'maestro' => array(
			'machine_name' => 'Maestro',
 			'method_name' => 'Maestro',
 			'parameters' => array(
				'pm' => 'CreditCard',
 				'brand' => 'Maestro',
 			),
 			'not_supported_features' => array(
				0 => 'AliasManager',
 				1 => 'ServerAuthorization',
 				2 => 'ExternalCheckout',
 			),
 			'credit_card_information' => array(
				'issuer_identification_number_prefixes' => array(
					0 => '5018',
 					1 => '5020',
 					2 => '5038',
 					3 => '6304',
 					4 => '6759',
 					5 => '6761',
 					6 => '6762',
 					7 => '6763',
 					8 => '6764',
 					9 => '6765',
 					10 => '6766',
 					11 => '564182',
 					12 => '633110',
 					13 => '6333',
 				),
 				'lengths' => array(
					0 => '12',
 					1 => '13',
 					2 => '14',
 					3 => '15',
 					4 => '16',
 					5 => '17',
 					6 => '18',
 					7 => '19',
 				),
 				'validators' => array(
					0 => 'LuhnAlgorithm',
 				),
 				'name' => 'Maestro',
 				'cvv_length' => '3',
 				'cvv_required' => 'false',
 				'issuer_number_length' => '2',
 				'issuer_number_required' => 'false',
 			),
 			'image_color' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAD0AAAAyCAYAAADvNNM8AAAE9UlEQVR42u2ZX2gcRRjAD3JXIkSI0MJxt7kd9vauJwk2tQEFA55UiKFilIq1Bg1SqWJaT1tq0FQiDXjiIUqPNkKL29wVxD+Qhz4EDRLTFluaYKp9yMOlvUrS3eXykIc85CEP4/ft7YaouWSz+yWndD/4uLvZmbnvN/N9M7Pf+HyeeOKJJ57cpzIblgU9FD+gC7GUGoqnNSGWUcOxXlWQO7Vggtnu6LtSnT+ntgVyWrc/r/XVDGpZ87MLylt9o9xfVdAiY/VaSO7Tw7FJLRzj62gR6vWXGAuu1lfNoN4BUCP+nLYIyiurPh/I63kYiORWw9ZqgtwDEPM2YP+pi9A2W9rRWId9ofE1Oa2wNmhFHQ4os81b48b2ZnZNvcse0fd8dvOiQ9iVuhQYVN/cNGCI1VYtFNfcAv8pNvEvX/mKv3NsjO88XeAE4By8ZYA83ktCVAaD59wC32vYyU8fHODHU6OGpt77hUfO3iEBhzUhTQcM8QcGT7kFRv2m/cNlYEvfPn6ZP3R+hmbG82onCTTE8BcUwBO72v8FbOnzfRNEs63P+5RSkMKtlyigM69dqAiNys7cpplt2NtdLl7yOQrg648+tyYw6osf3SCabW3Jd3FWcATMfUk/GLxAAX3+hU/XhUalim04wKQcxnJ8LwUw6sm3LtmC3v35FNVKPuJ0X05TAN96OGkLGLX91G90Lu5EwGCFAnrssZdtQ7/6wTUqaO4orsHgUQrokSe6bEMfPnGVDDqQU1uqBv1j6+tVgXb0JqYL8tD/eaa3KWrCyUwPUEDfaH7WNjThXs0xIbFx6FC8mwL6jrTbNvRTn/xOBT3jbPUOJhjVPr3eEdRS6cw00VFUPefmZWOSAnro6XfXBca3rdoLKtXhpM05dEg+RAE9LbXw94/+tCb0k+k/qBIKBVcJBfP8XaAA//aZnorAR46N8Qe/nqV6p97vPk3UILdRQN9lu/ipN37Y5DO3dqVxR2MdY6yeIpHQT5VIOHH0578Bd3w8QbdiK6UgAsuMPU6SQQGjv6c6i1vgB3uv8weUexTAC46OnbbiW5CzVOAvnfyVBBgWruK2nN60qblvXNEdJvqXE/545bP97PQef16fdAl9yXVObENXOkIsY9xY2IfFXJuCebfljmBrMe6tIB439jIBg7XVVzsr08N4SaeH43m8r1oliTgHx9lhvNjD25GKHQE8HigwaQ9Qt1YBnQMdhzq9W3KV4yDjsh2PsEWX20atojHUqt9UerIJAnssgz02+Z81MMZYc7Qh2gRGdiRhOwN7g5Io7sPvJkA9/N6PIFjXLAtiffistfqRIpG9UoPUgicpOcLSUZENRRnrMp5Be2yLis+h/ID1f1WBliKsDwycN4wUWVES2aQssiksRzgoW4xGmBIVxQUsMwZJZBr8zoKOYx/m917QjNHGqM9Gsb75fAn7L7cVx43/jLABGIx81aBBUzjqCGPMNMwYGi2LYjcaZ9brKQ+Q2A9awOc4EOglWAcHCqGxH3RtC9iEnkOvAE1Au+EV5TPVm2nTDXGmrZg0oBAIweA5DgjWBaA2wxvgXIxhYLpvJw4UlmNbc9Cu4efKfg3XhgGz2sL3K1WBxtiSIlKrOfIZ/IzDdoUzawLtM12x04pDjEl0YYxVI4ZF8RC6OLbDNljH9Ips2YPK/VprCLo1PmNlSVjtPPHEE088uR/kL0AODRA6ZWjXAAAAAElFTkSuQmCC',
 			'image_grey' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAD0AAAAyEAAAAACaz1CjAAAECUlEQVR42u3Yb0gbZxgA8OJg7Es+xA8lZKKDQBx2tJXqMdBiR2a6Kv1Q6EBjd8PRSzdLg51amlxC2sqscqYcom2NzLmU0i6T+a82iBARdHXZqFsqWZSu6GzVRk9rQyR2Sd7laqUmOe+5mJaNzTxfAveEH/e+7z33PNmB/rHPjm16m/5X0EzznXdsMzc0100dtUMWzzxXzurCxMpIiV3aPWmX3k2f7AllJUj7qHabNrlYHxmaUmvLk29fZrmaWquM+aR0Y1TXf9/5wLFF+tlw94XjWDS7Hnhv20G/GKEHDpM5Et0Ybd/MXIubZppj7zY6yrJvjm7OroVhjyM9Ltpt+bwPglW/n76pO6S7C+GktCuNe+c56DmrmgLhgtMmrSMcfwrB+xoF0X5xhRmCi/UnU57Dz4N8A8Z/tQigLT/BcOmhl7DWodsH09X13v0APWc9JoHpilMb6TB+C8a7JwHa/B4MfxYFhyME04Y9y5/w0MHC0iMwrUmLoQXt949NPPSYCoaL9WdCsbTuBEy3VvHQNzQwjDtj4TAtEbLkPPTVXTB9vIyL1iKYJqWRux1BVythmhjeOv1wKTE6uHU68l0WQV/64PXetefppvTXhIBK1spJh4TQqwub0n0MTB/7iPOEEzBc18Vzwj3zQp7rilMcJWUvTP/QwltI4QahWP/Fya1Vs4kVXnogU8CSv3n2SdRyn4Nhkzm6YYiig4XlQRgvuxNBd5Bvw/TYL+D7+jc3TJdoq67FV7/NuA9f9oINkrVFSLNw9q8XsEHI2fbuX/aOSgS0hfTHQmo5i+t26log+LwqsoDy0sHCtoOCXiT1MExVP94b5wgwkLn5ALA2BHTUPrzd2MkPW3pjezIBg4+Pum7CezkfL8nVXXNWNieUNVJS18XNNgKjDzDu+cVDlianpnS9WVRTte/aZpjmjTmhrImVrrSGt9bJr2ou7xtw8Y08cQ65Tyc98z6KP2cpbylPyIz5Xxntp2dHUl4p7ZKNK/uTAoSHsacGCISWvTbJ9KxLFn7HMf1J/lU2Z2jFKffhdXp1QzuDkE3ikrlkPrxngf1dAjSN7T6jbsg1FOQrBmnMw6R/WZmT4aYxlyxbaxQdXkTIKGqU1iR7mMqcIorGEJKNqRtcssOLNEYqyu8lRLcWBohsrYdxyosoy4ekAqErB2jMtDPvURGV4R5XkgrFYE1ygBhJYWGEMv/wr96f+vQS+/39qYRodhFzDexeFlHjygx3O5OtpbFBVJA/KrGnItQpdsoL8qdnnfIjF53ytVwfnveIvXrUmwDdn/RzLkI1yQgtqq4cQMieSio6xewu9ixU5tgkPvw7pVG0qEIoQNCYURQg2Fz2jJTfM4qmZ+9Psde2/zfbpv+f9N/ed38ctvffyAAAAABJRU5ErkJggg==',
 		),
 		'paypal' => array(
			'machine_name' => 'PayPal',
 			'method_name' => 'PayPal',
 			'parameters' => array(
				'pm' => 'PAYPAL',
 				'brand' => 'PAYPAL',
 			),
 			'not_supported_features' => array(
				0 => 'AliasManager',
 				1 => 'HiddenAuthorization',
 				2 => 'ServerAuthorization',
 				3 => 'Moto',
 				4 => 'ExternalCheckout',
 			),
 			'image_color' => 'data:image/png;base64,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',
 			'image_grey' => 'data:image/png;base64,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',
 		),
 		'masterpass' => array(
			'machine_name' => 'MasterPass',
 			'method_name' => 'MasterPass',
 			'parameters' => array(
				'pm' => 'MasterPass',
 				'brand' => 'MasterPass',
 			),
 			'not_supported_features' => array(
				0 => 'HiddenAuthorization',
 				1 => 'ServerAuthorization',
 				2 => 'AliasManager',
 			),
 			'image_color' => 'data:image/png;base64,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',
 			'image_grey' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAE8AAAAyEAAAAABocwUGAAAEt0lEQVR42u2Yb0gbZxjArxo9yrHJvGhwcT0h2y5b4g4NW4uZXA1WsptGltL7kLFYbiPCwQSrZuwM2cy6dEvdfahFpl2PWqvQfPCDAxEGx6g0gzCyEiGMbIatjDCksFWoA8Uw34Wi6UzyeqfVjd4Dd3A89/K753nf5x9Sd4iv/n7kMF9nz/4f8dD5p1uqcB1T2XX0z0J6mgH9hDFT32fMaC2agX3HK5NqOxs22hbs61vSerNho7az5E6uptbS+fvQ+UluamFLRMY1/uLpfcIrk4zTdu12sFzI5zGNJqt5bKyX2o6VKyLT+P2e49VNtN7Mh/ZQWn7SMZqBropcm+0kQ+f1E3uGV3KHul0MLSvtLV6qGFpWJBbGhhB4ZVLT+3Bwb9V9cM+3+Pn9KTMcon1QPd7Yaw44OMd9AAfkCwEOb2qhqUkl3sspOLg3Ur0Xs3BALv8A6+K6NRV4VTgcnH393eEtON+i/7tr1XCAIlOiVYo39noQ1rGD5u14/+xASAe33lOIp2+HtV33z7lwQK5+A4c3euXokCI82BP75uSjtgMy/Bus/egRBXjoPKzt3u7+N5xv8aMHsAGml1KA99wnsHj8szvh+Ra/OgmHd40vT+war74KFq/v6s54IzKse/MVCgXwYMOxfX3wj53x4MPziRd2jdccgoPr+HVnON/iRQIWL196K4BH34CNefnwLtTA4jl/2TfrtX99INY75HvvcZ5cY2bXeLWdauPeFd8+xj3VWeMZ2KzRTyrKuSc+hc25gumx59xdVSxG5RXLlxsKKxZkzHpaab332WW1QQWiWtaaYe3HPcipljvgq+VC04OivcZLfynqNc7AnlnDCoaxrIpO7dVuSAffftipDXfAOra5UXWfq9HAnuDd9rkdF/ZkSnBk+RU7ZIjBB+5OeeFayHyJbN9mLCd/rH6vPMFJxeGCS4X7WwUTKo2G/DZ3dJZvQmVY+fDjQme12GxAxXyvZqih+tTSI/O96pqhI8u5mjX0mZbgUi7YyOQ7p0yVhdruPZqOlsefcmnNWnNlFzpfUC9xbKy+r7nRVKmfwO4+mS3/B/Gmp/X6A8LjOPfmhaJuN89jGIIQhN9P0z09CEKSgYDXazA4HKmUKLrd4I3fb7MBHZYVBJJ0u61Wv99iAetQFPgOaFkswaDHo9OhqMcTDAJ9FXiZzKVLicTKSiiUSPT0YFgy6XIlEpnN4ntuThBYlqJIMhYDTxxfXT13Lp12OGg6k/F4MEyWZ2Z4fm3NYqEooLO8LMsIkkqBHyYIjpPltrb8+RYKLxoFfw7uDCNJLDs6ulkH6ldXga1isUAA6MgyQSCI251Oy3IyKUk0HQ5n3x8/jiCCEAoFAjyPIDYbwLPZkkmO26zG0XB4dlanU4UHFiQIcKdpSSKItTWr1WYD1jOZUFSSBAHsPZerooIkUymSNBgwDGhmv41EcFySnE6GSadJkufBOhYLsKvTSRA47nDEYqrwRBFBdDpwpyivF+zFSEQUZ2ZKS8fH4/G5OYoC9ohGx8cRhOdv3bp+3WTKagK8UCgaDQRQtLRUEKLRYFAUcXx2Nh4Ph3U6lo3HIxGOwzCGOZDAknX6oY17bW3grD8JyweH9zdUMvO8oj9pPQAAAABJRU5ErkJggg==',
 		),
 	);

	private $globalConfiguration = null;
	
	/**
	 * 
	 * @var Customweb_DependencyInjection_IContainer
	 */
	private $container = null;
	
	public function __construct(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod, Customweb_Barclaycard_Configuration $config) {
		parent::__construct($paymentMethod);
		$this->globalConfiguration = $config;
	}
	
	/**
	 *         	 				  				 	
	 * @return Customweb_Barclaycard_Configuration
	 */
	protected function getGlobalConfiguration() {
		return $this->globalConfiguration;
	}
	
	protected function getPaymentInformationMap() {
		return self::$paymentMapping;
	}
	
	/**
	 * This method returns a list of form elements. This form elements are used to generate the user input. 
	 * Sub classes may override this method to provide their own form fields.
	 * 
	 * @return array List of form elements
	 */
	public function getFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $authorizationMethod, $isMoto, $customerPaymentContext) {
		return array();
	}
	
	/**
	 * This method returns the parameters to add for processing an authorization request for this payment method. Sub classes
	 * may override this method. But they should call the parent and merge in their own parameters.
	 *
	 * @param Customweb_Barclaycard_Authorization_Transaction $transaction
	 * @param array $formData
	 * @return array
	 */
	public function getAuthorizationParameters(Customweb_Barclaycard_Authorization_Transaction $transaction, array $formData, $authorizationMethod) {
		$parameters = $this->getPaymentMethodBrandAndMethod($transaction);
		
		return $parameters;
	}
	
	public function getAliasGatewayAuthorizationParameters(Customweb_Barclaycard_Authorization_Transaction $transaction, array $formData, $authorizationMethod) {
		$parameters = $this->getPaymentMethodBrandAndMethod($transaction);
		return $parameters;
	}
	
	/**
	 * This method returns a map which contains the payment method and brand for the given payment method and transaction.
	 * The map has the following shape:
	 * array (
	 *    'pm' => 'Payment Method Name',
	 *    'brand' => 'Brand of the Payment Method',
	 * )
	 *
	 * @param Customweb_Barclaycard_Authorization_Transaction $transaction
	 * @return array Brand and Payment Method
	 */
	public function getPaymentMethodBrandAndMethod(Customweb_Barclaycard_Authorization_Transaction $transaction) {
		$params = $this->getPaymentMethodParameters();
		return array(
				'pm' => $params['pm'],
				'brand' => $params['brand'],
		);
	}
	
	public function getAliasCreationParameters(Customweb_Barclaycard_Authorization_Transaction $transaction){
		$parameters = array();
		$parameters['ALIASOPERATION'] = 'BYPSP';
		$parameters['ALIAS'] = '';
		return $parameters;
	}
	
	public function processAuthorization(Customweb_Barclaycard_AbstractAdapter $adapter, Customweb_Barclaycard_Authorization_Transaction $transaction, $parameters){
		$response = $adapter->processAuthorization($transaction, $parameters);
		return $response;
	}
	
	/**
	 * @Inject
	 */
	public function setContainer(Customweb_DependencyInjection_IContainer $container){
		$this->container = $container;
	}
	
	public function getContainer() {
		return $this->container;
	}
}