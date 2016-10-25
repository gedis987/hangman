<?php

function guess($letter, $id) {
	$game_results = "game.{$id}.txt";
	if (file_exists($game_results)) {
		$game = file_get_contents($game_results);
		$game = explode("|", $game);
		$letters_guessed = explode(",", $game[3]);
		if ($letters_guessed[0]) {
			if (in_array($letter, $letters_guessed)) {
				$json = array("status" => "success", "id" => $id, "message" => "Letter \"{$letter}\" was already guessed. Try another one. You still have {$game[0]} tries left. \n" . $game[2] . " \n Guessed letters:" . $game[3]);
				return $json;
			}
			$letters_guessed[] = $letter;
		} else {
			$letters_guessed[0] = $letter;
		}
		
		
		$lives = $game[0];
		$word = str_split($game[1]);
		$word_template = preg_replace("/\s/", "", $game[2]);
		$found = false;
		foreach ($word as $key => $word_letter) {
			if ($letter == $word_letter) {
				$found = true;
				$word_template[$key] = $letter;
			}
		}
		$word_template = str_split($word_template);
		$word_template = implode(" ", $word_template);

		if (!$found) {
			--$lives;
		}
		if ($lives <= 0) {
			$json = array("status" => "success", "id" => $id, "message" => "Game over. You have been hung up. Answer was: \n" . $game[1]);
			@unlink($game_results);
			$ids = file_get_contents("last_ids.txt");
			$ids = explode("|", $ids);
			$key = array_search($id, $ids);
			if (isset($key)) {
				unset($ids[$key]);
				file_put_contents("last_ids.txt", implode("|", $ids));
			}

			
		} else {
			$game[0] = $lives;
			$game[2] = $word_template;
			$game[3] = implode(",", $letters_guessed);
			file_put_contents($game_results, implode("|", $game));
			if ($found) {
				if (!preg_match('|_|Uis', $game[2])) {
					$json = array("status" => "success", "id" => $id, "message" => "You win! You found out the word. \n" . $game[2] . " \n Guessed letters:" . $game[3]);
					@unlink($game_results);
					$ids = file_get_contents("last_ids.txt");
					$ids = explode("|", $ids);
					$key = array_search($id, $ids);
					if (isset($key)) {
						unset($ids[$key]);
						file_put_contents("last_ids.txt", implode("|", $ids));
					}
				} else {
					$json = array("status" => "success", "id" => $id, "message" => "Letter \"{$letter}\" was found. You still have {$lives} tries left. \n" . $game[2] . " \n Guessed letters:" . $game[3]);
				}
			} else {
				$json = array("status" => "success", "id" => $id, "message" => "Letter \"{$letter}\" was not found. You have {$lives} tries left. \n" . $game[2] . " \n Guessed letters:" . $game[3]);
			}
			
		}
		return $json;

	} else {
		return false;
	}
}

function guess_word($word, $id) {
	$game_results = "game.{$id}.txt";
	if (file_exists($game_results)) {
		$game = file_get_contents($game_results);
		$game = explode("|", $game);
		if ($word == $game[1]) {
			$json = array("status" => "success", "id" => $id, "message" => "You win! You correctly guessed the word. \n" . $game[1] . " \n Guessed letters:" . $game[3]);
			@unlink($game_results);
			$ids = file_get_contents("last_ids.txt");
			$ids = explode("|", $ids);
			$key = array_search($id, $ids);
			if (isset($key)) {
				unset($ids[$key]);
				file_put_contents("last_ids.txt", implode("|", $ids));
			}
		} else {
			$lives = $game[0];
			--$lives;
			if ($lives <= 0) {
				$json = array("status" => "success", "id" => $id, "message" => "Game over. You have been hung up. Answer was: \n" . $game[1]);
				@unlink($game_results);
				$ids = file_get_contents("last_ids.txt");
				$ids = explode("|", $ids);
				$key = array_search($id, $ids);
				if (isset($key)) {
					unset($ids[$key]);
					file_put_contents("last_ids.txt", implode("|", $ids));
				}
			} else {
				$game[0] = $lives;
				file_put_contents($game_results, implode("|", $game));
				$json = array("status" => "success", "id" => $id, "message" => "That is not the correct word. You have {$lives} tries left. \n" . $game[2] . " \n Guessed letters:" . $game[3]);
			}

		}
		return $json;
	} else {
		return false;
	}
}


if (isset($_GET['letter'])) {
	if (isset($_GET['id'])) {
		$result = guess($_GET['letter'], $_GET['id']);
		if ($result) {
			$json = $result;
		} else {
			$json = array("status" => "error", "message" => "Something went wrong. Try new game!");
		}

	} else {
		$json = array("status" => "error", "message" => "Id not given!");
	}
} elseif (isset($_GET['word'])) {
	if (isset($_GET['id'])) {
		$result = guess_word($_GET['word'], $_GET['id']);
		if ($result) {
			$json = $result;
		} else {
			$json = array("status" => "error", "message" => "Something went wrong. Try new game!");
		}

	} else {
		$json = array("status" => "error", "message" => "Id not given!");
	}
} else {
	$json = array("status" => "error", "message" => "Id not given!");
}
echo json_encode($json);
exit;
