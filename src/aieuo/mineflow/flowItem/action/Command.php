<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\flowItem\base\PlayerFlowItem;
use aieuo\mineflow\flowItem\base\PlayerFlowItemTrait;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\Server;

class Command extends Action implements PlayerFlowItem {
    use PlayerFlowItemTrait;

    protected $id = self::COMMAND;

    protected $name = "action.command.name";
    protected $detail = "action.command.detail";
    protected $detailDefaultReplace = ["player", "command"];

    protected $category = Category::COMMAND;

    protected $targetRequired = Recipe::TARGET_REQUIRED_PLAYER;

    /** @var string */
    private $command;

    public function __construct(string $player = "target", string $command = "") {
        $this->setPlayerVariableName($player);
        $this->command = $command;
    }

    public function setCommand(string $health) {
        $this->command = $health;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function isDataValid(): bool {
        return $this->getPlayerVariableName() !== "" and $this->command !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerVariableName(), $this->getCommand()]);
    }

    public function execute(Recipe $origin) {
        $this->throwIfCannotExecute();

        $command = $origin->replaceVariables($this->getCommand());

        $player = $this->getPlayer($origin);
        $this->throwIfInvalidPlayer($player);

        Server::getInstance()->dispatchCommand($player, $command);
        yield true;
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@flowItem.form.target.player", "target", $default[1] ?? $this->getPlayerVariableName(), true),
                new ExampleInput("@action.command.form.command", "command", $default[2] ?? $this->getCommand(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        $this->setPlayerVariableName($content[0]);
        $this->setCommand($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerVariableName(), $this->getCommand()];
    }
}