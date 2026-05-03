<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $boleto_number
 * @property string|null $our_number
 * @property string|null $document_number
 * @property string|null $contract_number
 * @property string $payer_name
 * @property string $payer_document
 * @property string $payer_document_type
 * @property string|null $payer_email
 * @property string|null $payer_phone
 * @property string|null $payer_address
 * @property string|null $payer_city
 * @property string|null $payer_state
 * @property string|null $payer_zip_code
 * @property string|null $beneficiary_name
 * @property string|null $beneficiary_document
 * @property string|null $beneficiary_address
 * @property numeric $amount
 * @property numeric $discount_amount
 * @property numeric|null $discount_percentage
 * @property string|null $discount_limit_date
 * @property numeric $fine_amount
 * @property numeric $fine_percentage
 * @property numeric $interest_amount
 * @property numeric $interest_percentage
 * @property numeric $other_charges
 * @property numeric $total_amount
 * @property string $issue_date
 * @property string $due_date
 * @property string|null $processed_at
 * @property string|null $paid_at
 * @property string|null $cancelled_at
 * @property string|null $bank_code
 * @property string|null $bank_name
 * @property string|null $agency
 * @property string|null $account
 * @property string|null $wallet
 * @property string|null $agreement_number
 * @property string|null $barcode
 * @property string|null $digitable_line
 * @property string $description
 * @property string|null $instructions
 * @property string|null $additional_info
 * @property string $status
 * @property string|null $status_reason
 * @property numeric|null $paid_amount
 * @property string|null $credit_date
 * @property string|null $remessa_file
 * @property string|null $retorno_file
 * @property string|null $cnab_data
 * @property int $email_sent
 * @property string|null $email_sent_at
 * @property int $sms_sent
 * @property string|null $sms_sent_at
 * @property int $days_overdue_notification
 * @property int $is_recurring
 * @property string|null $recurrence_rule
 * @property string|null $recurrence_start
 * @property string|null $recurrence_end
 * @property int|null $recurrence_count
 * @property int|null $parent_boleto_id
 * @property string|null $category
 * @property string|null $tags
 * @property string|null $reference
 * @property string|null $pdf_path
 * @property string|null $attachments
 * @property string|null $notes
 * @property string|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereAdditionalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereAgency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereAgreementNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereBeneficiaryAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereBeneficiaryDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereBeneficiaryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereBoletoNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereCnabData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereContractNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereCreditDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDaysOverdueNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDigitableLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDiscountLimitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDiscountPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDocumentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereEmailSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereFineAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereFinePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereInterestAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereInterestPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereOtherCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereOurNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereParentBoletoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePayerZipCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto wherePdfPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereRecurrenceCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereRecurrenceEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereRecurrenceRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereRecurrenceStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereRemessaFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereRetornoFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereSmsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereSmsSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereStatusReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Boleto whereWallet($value)
 */
	class Boleto extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $created_by
 * @property string $transaction_id
 * @property string|null $gateway_transaction_id
 * @property string|null $gateway_reference
 * @property string|null $authorization_code
 * @property string|null $nsu
 * @property numeric $amount
 * @property numeric $original_amount
 * @property numeric $fee_amount
 * @property numeric $discount_amount
 * @property numeric $net_amount
 * @property int $installments
 * @property numeric|null $installment_amount
 * @property int $is_installment
 * @property string|null $installments_details
 * @property string $card_holder_name
 * @property string|null $card_last_digits
 * @property string|null $card_bin
 * @property string $card_brand
 * @property string $card_type
 * @property string|null $card_token
 * @property int|null $expiration_month
 * @property int|null $expiration_year
 * @property string $customer_name
 * @property string $customer_email
 * @property string $customer_document
 * @property string $customer_document_type
 * @property string|null $customer_phone
 * @property string|null $customer_ip
 * @property string|null $billing_address
 * @property string|null $billing_city
 * @property string|null $billing_state
 * @property string|null $billing_zip_code
 * @property string|null $billing_country
 * @property string $description
 * @property string|null $category
 * @property string|null $items
 * @property string|null $order_id
 * @property string $status
 * @property string|null $status_reason
 * @property string|null $gateway_status
 * @property string|null $authorized_at
 * @property string|null $captured_at
 * @property string|null $refunded_at
 * @property string|null $expected_payment_date
 * @property numeric|null $refunded_amount
 * @property string|null $refund_reason
 * @property string|null $refund_id
 * @property int $has_chargeback
 * @property numeric|null $chargeback_amount
 * @property string|null $chargeback_date
 * @property string|null $chargeback_reason
 * @property string|null $gateway
 * @property string|null $gateway_request
 * @property string|null $gateway_response
 * @property string|null $gateway_error
 * @property numeric|null $fraud_score
 * @property int|null $fraud_approved
 * @property string|null $antifraud_data
 * @property int $is_recurring
 * @property string|null $recurrence_id
 * @property int|null $recurrence_count
 * @property string|null $callback_url
 * @property string|null $return_url
 * @property string|null $receipt_path
 * @property string|null $attachments
 * @property string|null $notes
 * @property string|null $metadata
 * @property string|null $custom_fields
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereAntifraudData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereAuthorizationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereAuthorizedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereBillingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereBillingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereBillingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereBillingZipCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCallbackUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCapturedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCardBin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCardBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCardHolderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCardLastDigits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCardToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereChargebackAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereChargebackDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereChargebackReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCustomFields($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCustomerDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCustomerDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCustomerIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereCustomerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereExpectedPaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereExpirationMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereExpirationYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereFeeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereFraudApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereFraudScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereGateway($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereGatewayError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereGatewayReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereGatewayRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereGatewayResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereGatewayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereGatewayTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereHasChargeback($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereInstallmentAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereInstallments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereInstallmentsDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereIsInstallment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereIsRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereNetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereNsu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereReceiptPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereRecurrenceCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereRecurrenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereRefundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereRefundReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereRefundedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereRefundedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereReturnUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereStatusReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditCardTransaction whereUserId($value)
 */
	class CreditCardTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int|null $manager_id
 * @property int|null $parent_id
 * @property numeric|null $budget
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $location
 * @property int|null $capacity
 * @property int $is_active
 * @property string|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $department_id
 * @property int|null $supervisor_id
 * @property string|null $registration_number
 * @property string $position
 * @property string|null $role
 * @property string $employment_type
 * @property string $work_schedule
 * @property int $workload_hours
 * @property numeric $salary
 * @property string $salary_type
 * @property numeric|null $benefits_value
 * @property string|null $salary_history
 * @property string $hire_date
 * @property string|null $termination_date
 * @property string|null $probation_end_date
 * @property string|null $last_promotion_date
 * @property string|null $rg
 * @property string|null $issuer
 * @property string|null $birth_date
 * @property string|null $gender
 * @property string|null $marital_status
 * @property string|null $nationality
 * @property string|null $birth_place
 * @property string|null $personal_email
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip_code
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property string|null $emergency_contact_relationship
 * @property string|null $bank_name
 * @property string|null $bank_code
 * @property string|null $agency
 * @property string|null $account
 * @property string|null $account_type
 * @property string|null $pix_key
 * @property string|null $pis_pasep
 * @property string|null $ctps
 * @property string|null $ctps_serie
 * @property string|null $voter_id
 * @property string|null $military_id
 * @property string|null $photo_url
 * @property int $has_dependents
 * @property string|null $dependents_info
 * @property string|null $education_level
 * @property string|null $institution
 * @property string|null $course
 * @property string|null $graduation_year
 * @property int $has_health_plan
 * @property int $has_dental_plan
 * @property int $has_life_insurance
 * @property int $has_meal_voucher
 * @property int $has_food_voucher
 * @property int $has_transportation_voucher
 * @property int $has_gym_pass
 * @property numeric|null $meal_voucher_value
 * @property numeric|null $food_voucher_value
 * @property numeric|null $transportation_voucher_value
 * @property string $status
 * @property string|null $vacation_start_date
 * @property string|null $vacation_end_date
 * @property int $vacation_days_available
 * @property int $sick_days_available
 * @property string|null $last_evaluation_date
 * @property numeric|null $last_evaluation_score
 * @property string|null $evaluation_comments
 * @property string|null $observations
 * @property string|null $skills
 * @property string|null $certifications
 * @property string|null $languages
 * @property string|null $metadata
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAgency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBenefitsValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBirthPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCertifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCourse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCtps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCtpsSerie($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDependentsInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEducationLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmergencyContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmergencyContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmergencyContactRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmploymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEvaluationComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereFoodVoucherValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereGraduationYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasDentalPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasDependents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasFoodVoucher($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasGymPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasHealthPlan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasLifeInsurance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasMealVoucher($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasTransportationVoucher($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereInstitution($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereIssuer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereLanguages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereLastEvaluationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereLastEvaluationScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereLastPromotionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereMealVoucherValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereMilitaryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePersonalEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePhotoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePisPasep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePixKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereProbationEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereRg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSalaryHistory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSalaryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSickDaysAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSkills($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSupervisorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereTerminationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereTransportationVoucherValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereVacationDaysAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereVacationEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereVacationStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereVoterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereWorkSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereWorkloadHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereZipCode($value)
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $employee_id
 * @property int|null $uploaded_by
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property string|null $category
 * @property string $file_path
 * @property string $file_name
 * @property string $file_extension
 * @property string $file_mime_type
 * @property int $file_size
 * @property string $storage_disk
 * @property string|null $document_date
 * @property string|null $expiration_date
 * @property string|null $notification_date
 * @property string $status
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property string|null $rejection_reason
 * @property int $version
 * @property int $is_current
 * @property int|null $previous_version_id
 * @property string|null $tags
 * @property string|null $metadata
 * @property string|null $notes
 * @property int $is_private
 * @property int $requires_approval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereDocumentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereFileExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereFileMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereIsCurrent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereIsPrivate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereNotificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument wherePreviousVersionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereRequiresApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereStorageDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeDocument whereVersion($value)
 */
	class EmployeeDocument extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $employee_id
 * @property int|null $department_id
 * @property int|null $processed_by
 * @property string $reference_number
 * @property string $period
 * @property string $payment_date
 * @property int $year
 * @property int $month
 * @property string $type
 * @property string $employee_name
 * @property string $employee_cpf
 * @property string $employee_position
 * @property string $employee_department
 * @property numeric $base_salary
 * @property numeric|null $hourly_rate
 * @property int $worked_hours
 * @property int $worked_days
 * @property string|null $earnings
 * @property numeric $total_earnings
 * @property string|null $deductions
 * @property numeric $total_deductions
 * @property string|null $benefits
 * @property numeric $total_benefits
 * @property numeric $overtime_hours
 * @property numeric $overtime_amount
 * @property numeric $night_shift_hours
 * @property numeric $night_shift_amount
 * @property numeric $dangerousness_amount
 * @property numeric $unhealthiness_amount
 * @property numeric $bonus_amount
 * @property numeric $commission_amount
 * @property string|null $commissions_details
 * @property numeric $advance_amount
 * @property numeric $loan_amount
 * @property numeric $fgts_amount
 * @property numeric $inss_employer_amount
 * @property numeric $total_charges
 * @property string|null $taxes
 * @property numeric $irrf_amount
 * @property numeric $inss_amount
 * @property numeric $total_taxes
 * @property numeric $gross_salary
 * @property numeric $net_salary
 * @property numeric $total_cost
 * @property numeric $vacation_amount
 * @property numeric $vacation_bonus
 * @property numeric $thirteenth_amount
 * @property int|null $thirteenth_installment
 * @property string|null $termination_date
 * @property string|null $termination_type
 * @property numeric|null $termination_amount
 * @property string|null $termination_details
 * @property string $status
 * @property string|null $processed_at
 * @property string|null $rejection_reason
 * @property string|null $observations
 * @property string|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereAdvanceAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereBaseSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereBenefits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereBonusAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereCommissionAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereCommissionsDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereDangerousnessAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereDeductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereEmployeeCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereEmployeeDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereEmployeeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereEmployeePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereFgtsAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereGrossSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereInssAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereInssEmployerAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereIrrfAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereLoanAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereNetSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereNightShiftAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereNightShiftHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereObservations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereOvertimeAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereOvertimeHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereProcessedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTerminationAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTerminationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTerminationDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTerminationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereThirteenthAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereThirteenthInstallment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTotalBenefits($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTotalCharges($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTotalDeductions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTotalEarnings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereTotalTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereUnhealthinessAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereVacationAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereVacationBonus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereWorkedDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereWorkedHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereYear($value)
 */
	class Payroll extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $cpf
 * @property string|null $rg
 * @property string|null $phone
 * @property string|null $mobile
 * @property string|null $alternative_email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property string $locale
 * @property string $timezone
 * @property array<array-key, mixed>|null $preferences
 * @property string|null $profile_photo_path
 * @property bool $email_notifications
 * @property bool $sms_notifications
 * @property bool $push_notifications
 * @property bool $two_factor_enabled
 * @property string|null $notes
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Boleto> $boletos
 * @property-read int|null $boletos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditCardTransaction> $creditCardTransactions
 * @property-read int|null $credit_card_transactions_count
 * @property-read \App\Models\Employee|null $employee
 * @property-read string $profile
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $teams
 * @property-read int|null $teams_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User team($teams, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAlternativeEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCpf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePushNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSmsNotifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTeam($teams)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

