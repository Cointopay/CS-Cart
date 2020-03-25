<h4>Cointopay App Configuration</h4>

<div class="form-field">
	<label for="merchant_i">Merchant ID:</label>
	<input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}" class="input-text" />
</div>

<div class="form-field">
	<label for="secret_key">Security Code:</label>
	<input type="text" name="payment_data[processor_params][secret_key]" id="secret_key" value="{$processor_params.secret_key}" class="input-text" />
</div>
<div class="form-field">
	<label for="secret_key">API Key:</label>
	<input type="text" name="payment_data[processor_params][api_key]" id="api_key" value="{$processor_params.api_key}" class="input-text" />
</div>
