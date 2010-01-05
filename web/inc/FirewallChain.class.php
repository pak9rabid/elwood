<?php
	require_once "DataHash.class.php";

	class FirewallChain extends DataHash
	{
		// Methods
		public function commandOut()
		{
			$chain = $this->hashMap['CHAIN_NAME'];
			$policy = $this->hashMap['POLICY'];

			if (empty($chain))
				throw new Exception("Required attribute 'CHAIN_NAME' is empty");

			if (empty($policy))
				$policy = "-";

			return ":$chain $policy";
		}
	}
?>
