<?php
	require_once "DataHash.class.php";

	class FirewallChain extends DataHash
	{
		// Methods
		public function __toString()
		{
			$chain = $this->hashMap['CHAIN'];
			$policy = $this->hashMap['POLICY'];

			if (empty($chain))
				throw new Exception("Required chain is empty");

			if (empty($policy))
				$policy = "-";

			return ":$chain $policy";
		}
	}
?>
