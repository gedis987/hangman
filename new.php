<?php

function generate_id() {
	$last_id_file = "last_ids.txt";
	if (file_exists($last_id_file)) {
		$ids = file_get_contents($last_id_file);
		$ids = explode("|", $ids);
		for ($i=0; $i < 100; $i++) { 
			if (!in_array($i, $ids)) {
				$id = $i;
				break;
			}
		}
		if (isset($id)) {
			$ids[] = $id;
			file_put_contents($last_id_file, implode("|", $ids));
			return $id;
		} else {
			return null;
		}
	} else {
		file_put_contents($last_id_file, 0);
		return 0;
	}
}

function generate_word($id) {
	$game_results_file = "game.{$id}.txt";

	$json = file_get_contents('https://www.randomlists.com/data/words.json');
	if ($json) {
		$obj = json_decode($json, true);


		$words = array("house", "hangman", "sublime", "surrender");
		$key = rand(0, count($obj['data']) - 1);
		// $word = trim(preg_replace("|[a-z]|Uis", "_ ", $words[$key]));
		$word = trim(preg_replace("|[a-z]|Uis", "_ ", $obj['data'][$key]));
		// $results = "10|" . $words[$key] . "|" . $word . "|";
		$results = "10|" . $obj['data'][$key] . "|" . $word . "|";
		file_put_contents($game_results_file, $results);
		if (file_exists($game_results_file)) {
			return $word;
		} else {
			return false;
		}
	} else {
		return false;
	}
}


$id = generate_id();
if (isset($id)) {
	$word = generate_word($id);
	if ($word) {
		$json = array("status" => "success", "id" => $id, "message" => "guess the letter. You have 10 tries left. \n" . $word);
	} else {
		$json = array("status" => "error", "message" => "Something went wrong. Try again!");
	}

} else {
	$json = array("status" => "error", "message" => "Too many connections. Try again later!");
	
}

echo json_encode($json);
exit;

