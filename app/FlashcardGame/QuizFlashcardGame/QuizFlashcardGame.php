<?php

namespace App\FlashcardGame\QuizFlashcardGame;

use App\FlashcardGame\FlashcardGameInterface;
use App\Models\Flashcard;
use App\Models\Answer;
use App\Models\User;

class QuizFlashcardGame implements FlashcardGameInterface
{
    // User for current flashcard's game session
    protected $user;

    /**
     * Set a user for current flashcard game session
     *
     * @param  string  $username
     * @return void
     */
    public function setUser(string $username): void
    {
        $this->user = User::firstOrNew(["name" => strtolower(trim($username))]);

        // If user is new, then save it in DB
        if (!isset($this->user->id)) {
            $this->user->save();
        }
    }

    /**
     * Create a new flashcard in DB
     *
     * @param  string  $question
     * @param  string  $answer
     * @return void
     */
    public function createFlashcard(string $question, string $answer): void
    {
        $flashcard = new Flashcard();
        $flashcard->question = trim($question);
        $flashcard->answer = trim($answer);
        $flashcard->save();
    }

    /**
     * Get all flashcards from DB
     *
     * @return array
     */
    public function getAll(): array
    {
        return Flashcard::all("id", "question", "answer")->toArray();
    }

    /**
     * Get flashcard from DB by id
     *
     * @param  int    $id
     * @return array
     */
    public function getById($id): array
    {
        return Flashcard::find($id)->toArray();
    }

    /**
     * Validate and save answer for a flashcard
     *
     * @param  int     $flashcardId
     * @param  string  $answer
     * @return bool
     */
    public function validateAnswer(int $flashcardId, string $answer): bool
    {
        $flashcard = $this->getById($flashcardId);
        $isCorrectAnswer = false;

        if (!empty($flashcard)) {
            // Validate if answer is correct
            $isCorrectAnswer =
                strtolower($flashcard["answer"]) === strtolower(trim($answer));

            // Upsert the answer in DB
            Answer::updateOrCreate(
                ["user_id" => $this->user->id, "flashcard_id" => $flashcardId],
                [
                    "user_id" => $this->user->id,
                    "flashcard_id" => $flashcardId,
                    "is_correct" => $isCorrectAnswer,
                ]
            );
        }

        return $isCorrectAnswer;
    }

    /**
     * Get stats for current user's flashcard game
     *
     * @return array
     */
    public function getStats(): array
    {
        // Get stats from DB for current user
        $flashcards = Flashcard::leftJoin("answers", function ($join) {
            $join->on("flashcards.id", "=", "answers.flashcard_id");
            $join->where("answers.user_id", $this->user->id);
        })
            ->select("flashcards.id", "question", "is_correct")
            ->get()
            ->toArray();

        foreach ($flashcards as $key => $flashcard) {
            if ($flashcard["is_correct"] === null) {
                $flashcards[$key]["is_correct"] = "Not answered";
            } else {
                $flashcards[$key]["is_correct"] = $flashcard["is_correct"]
                    ? "Correct"
                    : "Incorrect";
            }
        }

        return $flashcards;
    }

    /**
     * Reset all answers of current user
     *
     * @return void
     */
    public function reset(): void
    {
        Answer::where("user_id", $this->user->id)->delete();
    }
}
