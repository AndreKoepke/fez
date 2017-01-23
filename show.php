<html>

	<?php include_once 'scripts/sql.php'; ?>

	<head>
		<title>FEZ</title>

		<script type="text/javascript" src="stopwatch.js"></script>
	</head>

	<body onload="javascript: window.setTimeout('location.reload()',5000); timer(document.getElementById('timer'));">
	<!-- <body onload="javascript: timer(document.getElementById('timer'));"> !-->
	<!--<body>!-->

		<?php
			$sql = new SQLConnection();
			$state = $sql->getState();
			$bool = false;
			$now = new DateTime;
			$alerted = $sql->getLastChangeTimestamp();
			$diff = $alerted->diff($now);


			switch ($state)
			{
				case "idle":
					echo "<h1>Kein Alarm</h1>";
					break;

				case "alert":
					echo "<h1>Alarmiert seit: <span id='timer'>".$diff->format("%H:%I:%S").".0</time></h1>";

					echo "<h2>Zur Wache:</h2><ul>";
					foreach ($sql->getAllWithState("come") as $row) {
						$bool = true;
						echo "<li>".$row['name']." (".$row['funktion'].")</li>";
					}

					if ($bool === false)
						echo "<li>Niemand</li>";

					$bool = false;

					echo "</ul><h2>Direkt:</h2><ul>";
					foreach ($sql->getAllWithState("come_direct") as $row) {
						$bool = true;
						echo "<li>".$row['name']." (".$row['funktion'].")</li>";
					}

					if ($bool === false)
						echo "<li>Niemand</li>";


					$bool = false;
					echo "</ul><h2>Kommen nicht:</h2><ul>";
					foreach ($sql->getAllWithState("na") as $row) {
						$bool = true;
						echo "<li>".$row['name']." (".$row['funktion'].")</li>";
					}

					if ($bool === false)
						echo "<li>Niemand</li>";

					echo "</ul>";

					break;

				default:
					echo "<h1>Falscher State ".$state."</h1>";


			}

		?>


	
	</body>

</html>