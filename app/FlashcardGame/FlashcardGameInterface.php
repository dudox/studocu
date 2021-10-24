<?php

namespace App\FlashcardGame;

interface FlashcardGameInterface
{
    /**
     * Set a user for current flashcard game session
     *
     * @param  string  $username
     * @return void
     */
    public function setUser(string $username): void;

    /**
     * Create a new flashcard in DB
     *
     * @param  string  $question
     * @param  string  $answer
     * @return void
     */
    public function createFlashcard(string $question, string $answer): void;

    /**
     * Get all flashcards from DB
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Get flashcard from DB by id
     *
     * @param  int    $id
     * @return array
     */
    public function getById($id): array;

    /**
     * Validate and save answer for a flashcard
     *
     * @param  int     $flashcardId
     * @param  string  $answer
     * @return bool
     */
    public function validateAnswer(int $flashcardId, string $answer): bool;

    /**
     * Get stats for current user's flashcard game
     *
     * @return array
     */
    public function getStats(): array;

    /**
     * Reset all answers of current user
     *
     * @return void
     */
    public function reset(): void;
}
