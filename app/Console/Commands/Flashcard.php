<?php

namespace App\Console\Commands;

use App\Models\Choice;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use App\FlashcardGame\FlashcardGameInterface;

class Flashcard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "flashcard:interactive";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Play flashcard game";

    protected $quizFlashcardGame;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FlashcardGameInterface $quizFlashcardGame)
    {
        parent::__construct();
        $this->quizFlashcardGame = $quizFlashcardGame;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->call("config:cache");

        // First, get username from user, to make it multiuser game
        $username = $this->getValidInput("Enter username");
        $this->quizFlashcardGame->setUser($username);

        $choice = null; // To hold input choice of user

        // Main menu for flashcard game
        $menu = [
            1 => "Create a flashcard",
            2 => "List all flashcards",
            3 => "Practice",
            4 => "Stats",
            5 => "Reset",
            6 => "Exit",
        ];

        do {
            $this->newLine(4);
            $this->alert("Quiz Flashcard Game (" . $username . ")");
            $choice = $this->choice("Select an option from menu below", $menu);

            switch ($choice) {
                case "Create a flashcard":
                    $question = $this->getValidInput(
                        "Enter a new flashcard question"
                    );
                    $answers = explode(',', $this->getValidInput("Write all the choices separated by a comma. The first one is the correct one"));
                    $correctChoice = array_shift($answers);

                    $this->quizFlashcardGame->createFlashcard(
                        $question,
                        $correctChoice,
                        $answers
                    );

                    break;

                case "List all flashcards":
                    $flashcards = $this->quizFlashcardGame->getAll();

                    // If flashcards are empty, then break
                    if (empty($flashcards)) {
                        $this->info(
                            "No flashcards found. Start creating them."
                        );
                        break;
                    }

                    // Display all flashcards on console
                    $this->showTable(
                        array_keys($flashcards[0]),
                        $flashcards,
                        "box"
                    );

                    break;

                case "Practice":
                    $flashcards = $this->getFlashcardStats();
                    $this->showFlashcardsWithStats($flashcards);

                    // If flashcards are empty, then break
                    if (empty($flashcards)) {
                        break;
                    }

                    // If all flashcards are answered, then don't take input
                    if ($this->isFlashcardComplete($flashcards)) {
                        $this->info(
                            "All flashcards already answered. Reset or add new flashcard."
                        );
                        break;
                    }

                    $flashcardId = $this->getValidInput(
                        "Which flashcard would you like to answer? Enter flashcard id"
                    );

                    $flashcard = $this->getFlashcardById(
                        $flashcards,
                        $flashcardId
                    );

                    if (empty($flashcard)) {
                        $this->error("Flashcard not found, invalid id given.");
                    } elseif ($flashcard["is_correct"] === "Correct") {
                        $this->error("Flashcard already answered correctly.");
                    } else {
                        $choices = Choice::where('flashcard_id', $flashcardId)->get();
                        $userChoice = $this->choice(
                            $flashcard["question"],
                            $choices->pluck('title')->all()
                        );

                        $answer = $choices->firstWhere('title', $userChoice);
                        $isCorrectAnswer = $answer->correct;

                        $this->quizFlashcardGame->validateAnswer(
                            $flashcardId,
                            $isCorrectAnswer
                        );

                        $this->info(
                            $isCorrectAnswer
                                ? "Correct Answer"
                                : "Incorrect Answer"
                        );
                    }

                    break;

                case "Stats":
                    // Get flashcard stats for this user
                    $flashcards = $this->getFlashcardStats();
                    $this->showFlashcardsWithStats($flashcards);
                    break;

                case "Reset":
                    // Delete all responses for this user
                    $this->quizFlashcardGame->reset();
                    $this->info("Reset done. All your answers are deleted.");
                    break;

                default:
                    break;
            }
        } while ($choice !== "Exit");

        return Command::SUCCESS;
    }

    /**
     * Accepting valid input from user
     *
     * @param  string  $message
     * @return string  $input
     */
    public function getValidInput(string $message): string
    {
        do {
            $input = trim($this->ask($message));
        } while ($input === "");

        return $input;
    }

    /**
     * Get current stats of flashcards
     *
     * @return array  $flashcards
     */
    public function getFlashcardStats(): array
    {
        $flashcards = $this->quizFlashcardGame->getStats();

        return $flashcards;
    }

    /**
     * Display flashcards with stats on console
     *
     * @param  array  $flashcards
     * @return void
     */
    public function showFlashcardsWithStats(array $flashcards): void
    {
        if (count($flashcards)) {
            $total = count($flashcards);
            $correct = 0;
            $incorrect = 0;

            foreach ($flashcards as $flashcard) {
                if ($flashcard["is_correct"] === "Correct") {
                    $correct++;
                } elseif ($flashcard["is_correct"] === "Incorrect") {
                    $incorrect++;
                }
            }

            $footerTitle = "Total: " . $total;
            $footerTitle .= ", Correct: " . $correct;
            $footerTitle .= ", Inorrect: " . $incorrect;

            $this->showTable(
                array_keys($flashcards[0]),
                $flashcards,
                "box",
                $footerTitle
            );

            $this->info(round(($correct * 100) / $total, 2) . "% completed.");
        } else {
            $this->info("No flashcards found. Start creating them.");
        }
    }

    /**
     * Find a flashcard in flashcards array
     *
     * @param  array  $flashcards
     * @param  int    $flashcardId
     * @return array  $flashcard
     */
    public function getFlashcardById(array $flashcards, int $flashcardId): array
    {
        foreach ($flashcards as $flashcard) {
            if ($flashcard["id"] === $flashcardId) {
                return $flashcard;
            }
        }

        return [];
    }

    /**
     * Display table of flashcards on console
     *
     * @param  array  $headers
     * @param  array  $rows
     * @param  string $tableStyle
     * @param  string $footerTitle
     * @return void
     */
    public function showTable(
        array $headers,
        array $rows,
        string $tableStyle = "default",
        string $footerTitle = null
    ): void {
        $table = new Table($this->output);

        $table
            ->setHeaders($headers)
            ->setRows($rows)
            ->setFooterTitle($footerTitle)
            ->setStyle($tableStyle);

        $table->render();
    }

    /**
     * Checks if all flashcards are answered correctly
     *
     * @param  array  $flashcards
     * @return bool
     */
    public function isFlashcardComplete(array $flashcards): bool
    {
        foreach ($flashcards as $flashcard) {
            if ($flashcard["is_correct"] !== "Correct") {
                return false;
            }
        }

        return true;
    }
}
