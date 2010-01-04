<?php
	require_once "DataHash.class.php";

	class FirewallRule extends DataHash
	{
		// Methods
		public function __toString()
		{
			$operation = $this->hashMap['OPERATION'];
			$chain = $this->hashMap['CHAIN'];
			$options = $this->hashMap['OPTIONS'];
			$errors = array();

			if (empty($operation))
				$errors[] = "Required operation missing";

			if (empty($chain))
				$errors[] = "Required chain missing";

			if (empty($options))
				$errors[] = "Required options missing";

			if (!empty($errors))
				throw new Exception(implode(", ", $errors));

			return "-$operation $chain $options";
		}
	}
?>
