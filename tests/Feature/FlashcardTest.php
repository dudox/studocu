<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use DB;

class FlashcardTest extends TestCase
{
    // Test username
    private $username = "testuser";

    // Flashcard menu
    private $menu = [
        1 => "Create a flashcard",
        2 => "List all flashcards",
        3 => "Practice",
        4 => "Stats",
        5 => "Reset",
        6 => "Exit",
    ];

    /**
     * Test for setting username.
     *
     * @return void
     */
    public function test_set_flashcard_user()
    {
        $this->artisan("config:cache --env=testing");

        // Reset testing DB
        $this->artisan("migrate:refresh");

        $users = DB::table("users")->get();

        $this->artisan("flashcard:interactive")
            ->expectsQuestion("Enter username", $this->username)
            ->expectsChoice(
                "Select an option from menu below",
                "Exit",
                $this->menu
            );
    }

    /**
     * Test for creating a flashcard.
     *
     * @return void
     */
    public function test_can_create_flashcard()
    {
        $this->artisan("flashcard:interactive")
            ->expectsQuestion("Enter username", $this->username)
            ->expectsChoice(
                "Select an option from menu below",
                "Create a flashcard",
                $this->menu
            )
            ->expectsQuestion("Enter a new flashcard question", "Question")
            ->expectsQuestion("Answer", "Answer")
            ->expectsChoice(
                "Select an option from menu below",
                "Exit",
                $this->menu
            );

        // Assert if flashcard is created in DB
        $this->assertDatabaseHas("flashcards", [
            "question" => "Question",
            "answer" => "Answer",
        ]);
    }

    /**
     * Test for answering a flashcard incorrectly.
     *
     * @return void
     */
    public function test_can_answer_flashcard_incorrectly()
    {
        $this->artisan("flashcard:interactive")
            ->expectsQuestion("Enter username", $this->username)
            ->expectsChoice(
                "Select an option from menu below",
                "Practice",
                $this->menu
            )
            ->expectsQuestion(
                "Which flashcard would you like to answer? Enter flashcard id",
                1
            )
            ->expectsQuestion("Question", "Wrong Answer")
            ->expectsOutput("Incorrect Answer")
            ->expectsChoice(
                "Select an option from menu below",
                "Exit",
                $this->menu
            );

        // Assert if incorrect answer is created in DB
        $this->assertDatabaseHas("answers", [
            "user_id" => 1,
            "flashcard_id" => 1,
            "is_correct" => 0,
        ]);
    }

    /**
     * Test for answering a flashcard correctly.
     *
     * @return void
     */
    public function test_can_answer_flashcard_correctly()
    {
        $this->artisan("flashcard:interactive")
            ->expectsQuestion("Enter username", $this->username)
            ->expectsChoice(
                "Select an option from menu below",
                "Practice",
                $this->menu
            )
            ->expectsQuestion(
                "Which flashcard would you like to answer? Enter flashcard id",
                1
            )
            ->expectsQuestion("Question", "Answer")
            ->expectsOutput("Correct Answer")
            ->expectsChoice(
                "Select an option from menu below",
                "Exit",
                $this->menu
            );

        // Assert if correct answer is updated in DB
        $this->assertDatabaseHas("answers", [
            "user_id" => 1,
            "flashcard_id" => 1,
            "is_correct" => 1,
        ]);
    }

    /**
     * Test for resetting all flashcard responses.
     *
     * @return void
     */
    public function test_can_reset_flashcard_answers()
    {
        $this->artisan("flashcard:interactive")
            ->expectsQuestion("Enter username", $this->username)
            ->expectsChoice(
                "Select an option from menu below",
                "Reset",
                $this->menu
            )
            ->expectsOutput("Reset done. All your answers are deleted.")
            ->expectsChoice(
                "Select an option from menu below",
                "Exit",
                $this->menu
            );
    }
}
