<?php
	require_once "DataHash.class.php";

	class FirewallRule extends DataHash
	{
		// Methods
		public function commandOut()
		{
			$operation = $this->hashMap['OPERATION'];
			$chain = $this->hashMap['CHAIN'];
			$options = $this->hashMap['OPTIONS'];
			$errors = array();

			if (empty($operation))
				$errors[] = "Required attribute 'OPERATION' is empty";

			if (empty($chain))
				$errors[] = "Required attribute 'CHAIN' is empty";

			if (empty($options))
				$errors[] = "Required attribute 'OPTIONS' is empty";

			if (!empty($errors))
				throw new Exception(implode(", ", $errors));

			return "-$operation $chain $options";
		}
	}
?>
