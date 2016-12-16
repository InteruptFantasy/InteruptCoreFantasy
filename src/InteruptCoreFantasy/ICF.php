<?php
//
// Project Name : InteruptCoreFantasy (icf)
// Summary : This Is Non Useable Yet Because It Still In Pseudo File!
//
namespace InteruptCoreFantasy;

use pocketmine\entity\{Entity, Attribute, Effect};
use pocketmine\nbt\tag\{CompoundTag, DoubleTag, FloatTag, ListTag};
use pocketmine\network\protocol\{MobEffectPacket, UpdateAttributesPacket};
use pocketmine\plugin\PluginBase;
use pocketmine\{Player, Server};
use pocketmine\level\particle\{AngryVillagerParticle, BubbleParticle, CriticalParticle, DustParticle, EnchantParticle, EnchantmentTableParticle, ExplodeParticle, FlameParticle, FloatingTextParticle, GenericParticle, HappyVillagerParticle, HeartParticle, HugeExplodeParticle, InkParticle, InstantEnchantParticle, ItemBreakParticle, LargeExplodeParticle, LavaDripParticle, LavaParticle, MobSpawnParticle, Particle, PortalParticle, RedstoneParticle, SmokeParticle, SplashParticle, SporeParticle, TerrainParticle, WaterDripParticle, WaterParticle};
use pocketmine\level\sound\{ExpPickupSound, ExplodeSound};
use pocketmine\utils\{Config, TextFormat as COLOR
};
use pocketmine\entity\{Creature, Effect, Entity, Human, Item as EntityItem, Living
};
use pocketmine\math\Vector3;
use SQLite3;
use pocketmine\block\Block;
use pocketmine\item\{Item, enchantment\Enchantment};
use pocketmine\level\{Level, Position};
use InteruptCoreFantasy\Tasks\{
    Envoy\StartEnvoyTask, Envoy\ClearEnvoyTask, ICFantasyFX\FXTask
};
use pocketmine\tile\{
    Tile, Chest, MobSpawner
};
use pocketmine\inventory\ChestInventory;
use pocketmine\nbt\NBT;
use pocketmine\level\sound\{
    ExpPickupSound, ExplodeSound
};
use InteruptCoreFantasy\PiggyAuth\{Commands\ChangePasswordCommand, Commands\ChangeEmailCommand, Commands\ForgotPasswordCommand, Commands\LoginCommand, \Commands\LogoutCommand, Commands\KeyCommand, Commands\PinCommand, Commands\PreregisterCommand, Commands\RegisterCommand, Commands\ResetPasswordCommand, Commands\SendPinCommand, Databases\MySQL, Databases\SQLite3, Entities\Wither, Packet\BossEventPacket, Tasks\AttributeTick, Tasks\KeyTick, Tasks\MessageTick, Tasks\PingTask, Tasks\PopupTipBarTick, Tasks\TimeoutTask};


class ICF extends PluginBase implements Listener {

	const RARITY_TYPE_COMMON = 0;//COMMON RARITY
	const RARITY_TYPE_UNCOMMON = 1;//UNCOMMON RARITY
	const RARITY_TYPE_RARE = 2;//RARE RARITY
	const RARITY_TYPE_MYSTICS = 3;//MYSTICS RARITY
    const RARITY_TYPE_LEGENDARY = 4;//LEGENDARY RARITY
    const RARITY_TYPE_ANCIENT = 5;//ANCIENT RARITY
    const RARITY_TYPE_PHANTAMS = 6;//PHANTASM RARITY
    const RARITY_TYPE_LOST = 7;//LOST RARITY
    const RARITY_TYPE_GODLY = 8;//GODLY RARITY

	const TYPE_INVALID = -1;//ANY ERROR OR BUGS WILL BE REPORTED 

    const CLASS_TYPE_DEFAULT = 101;//101 ~ 200 = DEFAULT CLASS
    const CLASS_TYPE_WARRIOR = 201;//201 ~ 300 = WARRIOR CLASS + BRANCH CLASS
    const CLASS_TYPE_ARCHER = 301;//301 ~ 400 = ARCHER CLASS + BRANCH CLASS
    const CLASS_TYPE_MAGICIAN = 401;//401 ~ 500 = MAGICIAN CLASS + BRANCH CLASS
    const CLASS_TYPE_MARTIAL_ART = 501;//501 ~ 600 = MARTIAL ART CLASS + BRANCH CLASS
    const CLASS_TYPE_THEIF = 601;//601 ~ 700 = THEIF CLASS + BRANCH CLASS
    const CLASS_TYPE_PIRATE = 701;//701 ~ 800 = PIRATE CLASS + BRANCH CLASS
    const CLASS_TYPE_MECHANIC = 801;//801 ~ 900 = MECHANIC CLASS + BRANCH CLASS
    const CLASS_TYPE_ETC = 901;//901 ~ 1000 = ETC CLASS [ OPEN CLASS ]
    const CLASS_TYPE_SOUL_MASTER = 1001;//ONLY FOR SECRET TYPE RACES

    const RACES_TYPE_DEFAULT = 10001;//10001 ~ 20000 = DEFAULT RACES
    const RACES_TYPE_HUMAN = 20001;//20001 ~ 30000 = HUMAN RACES
    const RACES_TYPE_BEAST = 30001;//30001 ~ 40000 = BEAST RACES
    const RACES_TYPE_ELF = 40001;//40001 ~ 50000 = ELF RACES
    const RACES_TYPE_DEMON = 50001;//50001 ~ 60000 = DEMON RACES
    const RACES_TYPE_DEMI_HUMAN = 60001;//60001 ~ 70000 = DEMI HUMAN RACES
    const RACES_TYPE_DARK_ELF = 70001;//70001 ~ 80000 = DARK ELF RACES
    const RACES_TYPE_FISHMEN = 80001;//80001 ~ 90000 = FISHMEN RACES
    const RACES_TYPE_DWARVES = 90001;//90001 ~ 100000 = DWARVES RACES
    const RACES_TYPE_SECRET = 100001;//UNKNOWN ENTITY [ ONLY GIVEN TO GAMEMASTER RANKS ]

    public static $random;

    const SCREEN_MODE_FRONT = 90909090;//PLAYER VIEW INFRONT
    const SCREEN_MODE_MIDDLE = 9090909090;//PLAYER VIEW MIDDLE
    const SCREEN_MODE_BACK = 909090909090;//PLAYER VIEW OUTBACK

    private static $change;

    const GAME_MASTER = "GM";//GAME MASTER [ ONLY GIVEN TO SPECIFIC PLAYER ]
    const DEFAULT = "";//ONLY GIVEN TO NORMAL PLAYER
    const DONATOR = "#$$$#";
    const MAP = array[];//MAP SHOWS A SPECIFIC MAP
    const SURVIVAL = 0;//GAMEMODE SURVIVALS
    const CREATIVE = 1;//GAMEMODE CREATIVE
    const ADVENTURE = 2;//GAMEMODE ADVENTURE
    const VIEW = ICF::ADVENTURE;//DEFAULT VIEW
    const PLAYER_SPEED = 1;//PLAYER SPEED
    const JUMP = 1.3;//PLAYER JUMP
    const CHARACTERS = "||ABCDEFGHIJKLMNOPQRSTUVWXYZ||abcdefghijklmnopqrstuvwxyz||0123456789||!@#$%^&*()||";//CHARACTER
    const ALLOWED_CHARS = ICF::CHARACTERS;//ALLOWED CHARACTERS
    const ADMIN = "Admin";//GIVEN TO ADMIN
    const NPC = "NPC";//NPC [ NPC AI ]
    const FILE = [];//FILE 
    const EMPTY = null;//NULL 
    const INVENTORIES_SLOT = 60;//DEFAULT INENTORIES SLOTS
    const LOCK_SLOT = 40;//LOCKED SLOTS
    const COMMANDS = "";//COMMANDS

    public $player;
    public $skills;
    public $gamemode;
    public $speed = null;
    public $allowFlight = false;
    public $inventories;
    private $date;
    private $npc;
    public $admin;
    public $items = array[];
    public $msg;
    public $authenticated;
    public $confirmPassword;
    public $confirmedPassword;
    public $giveEmail;
    public $keepCape;
    public $joinMessage;
    public $gamemode;
    public $messagetick;
    public $timeouttick;
    public $tries;
    public $database;
    public $wither;
    private $key = "90fdh397hjh355bjdkhjhjdyjbaabjkf830237";//I just put a random keywords //lol
    public $keytime = 299; //300 = Reset
    public $expiredkeys = [];
    private static $NAME = "InteruptCoreFantasy";
    public static $indexofgreatness = ["mystics","holy","legends","void","enchanter"];//i change it name cause it will make a confusing to people
    public static $ench = ["1"];
    public $config;
    public $data;
    public $disableItems = array();
    private $items = [];
    public $message = "";
    public $messages;
    public $players = array();
    protected $exemptedEntities = [];
    public $blockedcommands = array();
    public $cmd;
    public $db;
    public $dbcreative;
    public $interval = 10;
    public $talked = [];
    public $victim = [];
    public $warnings = [];
    public $queue = [];
    private $commands = [];
    public $using = array();

    public function onLoad(){
        @mkdir($this->getDataFolder());//get The Data Folder
        $data = array("translate.yml", "config.yml", "chat.yml", "custom.yml", "text.yml", "vote.yml", "coordinate.yml", "rpg.yml");//Folder In Array Form
        foreach ($data as $file){//The Data Array As $file
            if (!file_exists($this->getDataFolder() . $file)){//If Not Exists , get Data Folder . [ the array data folder ]
                @mkdir($this->getDataFolder());//mkdir get Data Folder
                file_put_contents($this->getDataFolder() . $file, $this->getResource($file));//input content into data Folder
            }
        }
        if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());// data folder mkdir get Data Folder
        if (!is_dir($this->getDataFolder() . "players/")) mkdir($this->getDataFolder() . "players/");// get Data Folder [ player ]
        if (!is_dir($this->getDataFolder() . "vaults/")) mkdir($this->getDataFolder() . "vaults/");// get Data Folder [ vault ]
        if (!is_dir($this->getDataFolder() . "/bounties")) mkdir($this->getDataFolder() . "/bounties");// get Data Folder [ bounties ]
        if (!is_dir($this->getDataFolder() . "Lists/")) mkdir($this->getDataFolder() . "Lists/");// get Data Folder [ lists ]
        $this->vote = new Config($this->getDataFolder() . "vote.yml", Config::YAML);// vote folder
        $this->block = new Config($this->getDataFolder() . "blocks.json", Config::JSON);// json folder [ blocks ]
        $this->backups = new Config($this->getDataFolder() . "backups.txt", Config::ENUM);
        $this->dbcreative = new SQLite3($this->getDataFolder() . "blockscreative.bin");
        $this->coordinates = new Config($this->getDataFolder() . "coordinates.yml", Config::YAML);
        $this->particleData = new Config($this->getDataFolder() . "particles.yml", Config::YAML, array());
        $this->translate = new Config($this->getDataFolder() . "translate.yml", Config::YAML);
        $this->text = new Config($this->getDataFolder() . "text.yml", Config::YAML);
        $this->custom = new Config($this->getDataFolder() . "custom.yml", Config::YAML);
        $this->chat = new Config($this->getDataFolder() . "chat.yml", Config::YAML);
        $this->rpg = new Config($this->getDataFolder() . "rpg.yml", Config::YAML);
        $this->customEnch = new CustomEnchants();
        $this->customItem = new CustomItems();
        $this->antiHack = new AntiHacks();
        $this->class = new Class();
        $this->races = new Races();
        $this->skills = new Skills();
        $this->power = new Power();
        $this->events = new Events();
        $this->npc = new NPC();
        $this->ai = new ArtificialIntelect();
        $this->monster = new Monster();
        $this->dbcreative->exec("CREATE TABLE IF NOT EXITS blocks(world varchar(60), location varchar(10000000));");
        $this->reward = new Reward($this);
        $this->stackHeartbeat = new StackHeartbeat($this);
        $this->spReward = new SpecialsReward($this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new SpawnFly($this, 20), 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Scheduler($this, $this->interval), 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $saveRes = array("obsidian.json", "values.txt", "vote.yml", "limitedcreative.yml");
        $this->lists = [];
        $this->reloadVote();
        $this->essentialsPE = $this->getServer()->getPluginManager()->getPlugin("EssentialsPE");
        $this->purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->pureChat = $this->getServer()->getPluginManager()->getPlugin("PureChat");
        foreach ($saveRes as $resource){
            $this->saveResource($resource);
        }
    }

    public function onEnable() {
        $commandmap = $this->getServer()->getCommandMap();
        $scheduler = $this->getServer()->getScheduler();
        $this->saveDefaultConfig();
        $commandmap->register('changepassword', new ChangePasswordCommand('changepassword', $this));
        $commandmap->register('changeemail', new ChangeEmailCommand('changeemail', $this));
        $commandmap->register('forgotpassword', new ForgotPasswordCommand('forgotpassword', $this));
        $commandmap->register('key', new KeyCommand('key', $this));
        $commandmap->register('login', new LoginCommand('login', $this));
        $commandmap->register('logout', new LogoutCommand('logout', $this));
        $commandmap->register('pin', new PinCommand('pin', $this));
        $commandmap->register('preregister', new PreregisterCommand('preregister', $this));
        $commandmap->register('register', new RegisterCommand('register', $this));
        $commandmap->register('resetpassword', new ResetPasswordCommand('resetpassword', $this));
        $commandmap->register('sendpin', new SendPinCommand('sendpin', $this));
        $scheduler->scheduleRepeatingTask(new AttributeTick($this), 20);
        $scheduler->scheduleRepeatingTask(new MessageTick($this), 20);
        if ($this->getConfig()->get("key")) {
            $scheduler->scheduleRepeatingTask(new KeyTick($this), 20);
        }
        if ($this->getConfig()->get("popup") || $this->getConfig()->get("tip") || $this->getConfig()->get("boss-bar")) {
            $scheduler->scheduleRepeatingTask(new PopupTipBarTick($this), 20);
        }
        if($this->getConfig()->get("timeout")) {
            $scheduler->scheduleRepeatingTask(new TimeoutTask($this), 20);
        }
        if($this->getConfig()->get("boss-bar")) {
            Entity::registerEntity(Wither::class);
            $this->getServer()->getNetwork()->registerPacket(BossEventPacket::NETWORK_ID, BossEventPacket::class);
    }
    $outdate = false;
    if ($this->getConfig()->get("config->update")){
        if (!$this->getConfig()->exists("version")){
            rename($this->getDataFolder() . "Authentication.yml", $this->getDataFolder() . "Authentication_old.yml");
            $this->saveDefaultConfig();
            $outdate = true;
        }
        elseif ($this->getConfig()->get("version") !== $this->getDescription()->getVersion()){
            switch ($this->getConfig()->get("version")){
                case "0.0.1":
                case "0.0.2":
                rename($this->getDataFolder() . "Authentication.yml", $this->getDataFolder() . "Authentication_old.yml");
                $this->saveDefaultConfig();
                $outdate = true;
                break;
            }
            $this->getConfig()->set("version", $this->getDescription()->getVersion());
            $this->getConfig()->save();
        }
    }
    switch ($this->getConfig()->get("database")){
        case "mysql":
        $this->database = new MySQL($this, $outdate);
        $this->getServer()->getScheduler()->schedulerRepeatingTask(new PingTask($this, $this->database), 300);
        break;
        case "sqlite3":
        $this->database = new SQLite3($this, $outdate);
        break;
        default:
        $this->database = new SQLite3($this, $outdate);
        break;
    }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            $this->startSession($player);
        }
    $this->getLogger()->info($this->c("6", "test") . "DADADAAAAAAA~~~")
 }
    public function onDisable(){
        $this->data->save(true);
        $this->particleData->save(true);
        $this->vote->save(true);
    }
    public function c($c, $e){
        if ($c === "a") $a = COLOR::GREEN;
        if ($c === "b") $a = COLOR::AQUA;
        if ($c === "c") $a = COLOR::RED;
        if ($c === "e") $a = COLOR::YELLOW;
        if ($c === "f") $a = COLOR::WHITE;
        if ($c === "i") $a = COLOR::ITALIC;
        if ($c === "l") $a = COLOR::BOLD;
        if ($c === "r") $a = COLOR::RESET;
        if ($e === "icf"){
            return COLOR::GOLD . "[" . COLOR::BOLD . COLOR::AQUA . "#" . COLOR::RESET . COLOR::GOLD . "]" . COLOR::WHITE . COLOR::RESET . $a );
        }elseif ($e === "!"){
            return COLOR::BOLD . COLOR::RED . $a . "(!)" . COLOR::RESET . $a;
        }elseif ($e === "award"){
            return COLOR::BOLD . COLOR::GOLD . "(" . COLOR::GREEN . "$" . COLOR::GOLD . ")" . COLOR::RESET . $a;
        }elseif ($e === "npc"){
            return COLOR::GOLD . $a;
        }elseif ($e === "test"){
            return COLOR::DARK_RED . "lolololololololol" . $a;
        }
    }
    public function calculateExpReduction($p, $exp){
        if($p instanceof Human){
            $xp = $p->getTotalXp();
            $p->setTotalXp($xp - $exp);
        }
    }

    public function redeemExp($player, $exp){
		    if($player instanceof Human){
            $currentExp = $player->getTotalXp();
            if ($exp > 32000) {
                $player->sendMessage(TF::RED . TF::BOLD . "(!) " . TF::RESET . TF::RED . "You cannot redeem more than 32000 XP at once.");
                return false;
            }
        		if ($currentExp >= $exp) {
                $this->calculateExpReduction($player, $exp);
                $xpBottle = Item::get(384, $exp, 1);
                $xpBottle->setCustomName(TF::GREEN . TF::BOLD . "Experience Bottle " . TF::RESET . TF::GRAY . "(Throw)\n" . TF::LIGHT_PURPLE . "Value " . TF::WHITE . $exp . "\n" . TF::LIGHT_PURPLE . "Enchanter " . TF::WHITE . $player->getName());
                $player->getInventory()->addItem($xpBottle);
                $player->sendMessage(TF::GREEN . TF::BOLD . "XPBottle " . TF::RESET . TF::GREEN . "You have successfully redeemed " . TF::YELLOW . $exp . TF::GREEN . ".");
                $player->getLevel()->addSound(new ExpPickupSound($player), [$player]);
            } else {
                $player->sendMessage(TF::RED . TF::BOLD . "XPBottle " . TF::RESET . TF::RED . "You don't have enough experience. Your current experience is " . TF::YELLOW . $currentExp);
        		}
        }
    }

    public function sendExperienceStatistics($p){
        if($p instanceof Human){
            $difference = $p->getLevelXpRequirement($p->getXpLevel()) - $p->getFilledXp();
            $p->sendMessage(TF::GOLD . "You have " . TF::RED . $p->getFilledXp() . TF::GOLD . " exp (level " . TF::RED . $p->getXpLevel() . TF::GOLD . ") and need " . TF::RED . $difference . TF::GOLD . " more exp to level up.");
        }
    }
    public function getDatabase(){
        return $this->database;
    }
    public static function parseSpawnerList(array $list){
        $spawners = [];
        foreach ($list as $data) {
            $temp = explode(", ", (string)$data);
            $meta = (int)$temp[0];
            if (isset($temp[2])) {
                $spawners[$meta] = [
                    "meta" => $meta,
                    "id" => (int)$temp[1],
                    "name" => "§6$temp[2]"
                ];
            } elseif (isset($temp[1])) {
                $spawners[$meta] = ["meta" => $meta,
                    "id" => (int)$temp[1],
                    "name" => "§6Mob Spawner"
                ];
            }
            continue;
        }
        return $spawners;
    }
    public static function getSpawnerMetaFromId($id, array $spawnerData){
        foreach ($spawnerData as $data) {
            if ($data["id"] === $id) return (int)$data["meta"];
        }
        return false;
    }

    public static function isStack($entity){
        if (!$entity instanceof Player) {
            return $entity instanceof Living and (!$entity instanceof Item) and isset($entity->namedtag->StackData);
        }
    }

    public static function getStackSize(Living $entity){
        if (!$entity instanceof Player && isset($entity->namedtag->StackData->Amount) && $entity->namedtag->StackData->Amount instanceof IntTag) {
            return $entity->namedtag->StackData["Amount"];
        }
        return 1;
    }

    public static function increaseStackSize(Living $entity, $amount = 1){
        if (!$entity instanceof Player && self::isStack($entity) && isset($entity->namedtag->StackData->Amount)) {
            $entity->namedtag->StackData->Amount->setValue(self::getStackSize($entity) + $amount);
            return true;
        }
        return false;
    }

    public static function decreaseStackSize(Living $entity, $amount = 1){
        if (!$entity instanceof Player && self::isStack($entity) && isset($entity->namedtag->StackData->Amount)) {
            $entity->namedtag->StackData->Amount->setValue(self::getStackSize($entity) - $amount);
            return true;
        }
        return false;
    }

    public static function createStack(Living $entity, $count = 1){
        if (!$entity instanceof Player) {
            $entity->namedtag->StackData = new CompoundTag("StackData", [
                "Amount" => new IntTag("Amount", $count),
            ]);
        }
    }

    public static function addToStack(Living $stack, Living $entity){
        if (!$entity instanceof Player && is_a($entity, get_class($stack)) && $stack !== $entity) {
            if (self::increaseStackSize($stack, self::getStackSize($entity))) {
                $entity->close();
                return true;
            }
        }
        return false;
    }

    public static function removeFromStack(Living $entity){
        if (!$entity instanceof Player) {
            if (self::decreaseStackSize($entity)) {
                if (self::getStackSize($entity) <= 0) return false;
                $level = $entity->getLevel();
                $pos = new Vector3($entity->x, $entity->y + 1, $entity->z);
                $server = $level->getServer();
                $server->getPluginManager()->callEvent($ev = new \pocketmine\event\entity\EntityDeathEvent($entity, $entity->getDrops()));
                foreach ($ev->getDrops() as $drops) {
                    $level->dropItem($pos, $drops);
                }
                if ($server->expEnabled) {
                    $exp = mt_rand($entity->getDropExpMin(), $entity->getDropExpMax());
                    if ($exp > 0) $level->spawnXPOrb($entity, $exp);
                }
                return true;
            }
            return false;
        }
    }

    public static function recalculateStackName(Living $entity, Config $settings){
        if (!$entity instanceof Player) {
            assert(self::isStack($entity));
            $count = self::getStackSize($entity);
            $entity->setNameTagVisible(true);
            $entity->setNameTag(TF::YELLOW . TF::BOLD . $count . "X " . strtoupper($entity->getName()));
        }
    }

    public static function findNearbyStack(Living $entity, $range = 16){
        if (!$entity instanceof Player) {
            $stack = null;
            $closest = $range;
            $bb = $entity->getBoundingBox();
            $bb = $bb->grow($range, $range, $range);
            foreach ($entity->getLevel()->getCollidingEntities($bb) as $e) {
                if (is_a($e, get_class($entity)) and $stack !== $entity) {
                    $distance = $e->distance($entity);
                    if ($distance < $closest) {
                        if (!self::isStack($e) and self::isStack($stack)) continue;
                        $closest = $distance;
                        $stack = $e;
                    }
                }
            }
            return $stack;
        }
    }

    public static function addToClosestStack(Living $entity, $range = 16, Config $settings){
        $stack = self::findNearbyStack($entity, $range);
        if (self::isStack($stack)) {
            if (self::addToStack($stack, $entity)) {
                self::recalculateStackName($stack, $settings);
                return true;
            }
        } else {
            if ($stack instanceof Living && !$stack instanceof Player) {
                self::createStack($stack);
                self::addToStack($stack, $entity);
                self::recalculateStackName($stack, $settings);
                return true;
            }
        }
        return false;
    }
    public function register(Player $player, $password, $confirmpassword, $email = "none", $xbox = "false") {
        if(isset($this->confirmPassword[strtolower($player->getName())])) {
            unset($this->confirmPassword[strtolower($player->getName())]);
        }
        if($this->isRegistered($player->getName())) {
            $player->sendMessage($this->getMessage("already-registered"));
            return false;
        }
        if(strlen($password) < $this->getConfig()->get("minimum-password-length")) {
            $player->sendMessage($this->getMessage("password-too-short"));
            return false;
        }
        if(in_array(strtolower($password), $this->getConfig()->get("blocked-passwords")) || in_array(strtolower($confirmpassword), $this->getConfig()->get("blocked-passwords"))) {
            $player->sendMessage($this->getMessage("password-blocked"));
            return false;
        }
        if(strtolower($password) == strtolower($player->getName()) || strtolower($password) == preg_replace("/\d/", "", strtolower($player->getName())) || preg_replace("/\d/", "", strtolower($password)) == strtolower($player->getName()) || preg_replace("/\d/", "", strtolower($password)) == preg_replace("/\d/", "", strtolower($player->getName()))) {
            $player->sendMessage($this->getMessage("password-username"));
            return false;
        }
        if($password !== $confirmpassword) {
            $player->sendMessage($this->getMessage("password-not-match"));
            return false;
        }
        $this->database->insertData($player, $password, $email, $xbox);
        $this->force($player, false, $xbox == "false" ? 0 : 3);
        if($this->getConfig()->get("progress-reports")) {
            if($this->database->getRegisteredCount() / $this->getConfig()->get("progress-report-number") >= 0 && floor($this->database->getRegisteredCount() / $this->getConfig()->get("progress-report-number")) == $this->database->getRegisteredCount() / $this->getConfig()->get("progress-report-number")) {
                $this->emailUser($this->getConfig()->get("progress-report-email"), "Server Progress Report", str_replace("{port}", $this->getServer()->getPort(), str_replace("{ip}", $this->getServer()->getIP(), str_replace("{players}", $this->database->getRegisteredCount(), str_replace("{player}", $player->getName(), $this->getMessage("progress-report"))))));
            }
        }
        return true;
    }
    public function preregister($sender, $player, $password, $confirmpassword, $email = "none") {
        if(isset($this->confirmPassword[strtolower($player)])) {
            unset($this->confirmPassword[strtolower($player)]);
        }
        if($this->isRegistered($player)) {
            $sender->sendMessage($this->getMessage("already-registered-two"));
            return false;
        }
        if(strlen($password) < $this->getConfig()->get("minimum-password-length")) {
            $sender->sendMessage($this->getMessage("password-too-short"));
            return false;
        }
        if(in_array(strtolower($password), $this->getConfig()->get("blocked-passwords")) || in_array(strtolower($confirmpassword), $this->getConfig()->get("blocked-passwords"))) {
            $sender->sendMessage($this->getMessage("password-blocked"));
            return false;
        }
        if(strtolower($password) == strtolower($player) || strtolower($password) == preg_replace("/\d/", "", strtolower($player)) || preg_replace("/\d/", "", strtolower($password)) == strtolower($player)) {
            $sender->sendMessage($this->getMessage("password-username"));
            return false;
        }
        if($password !== $confirmpassword) {
            $sender->sendMessage($this->getMessage("password-not-match"));
            return false;
        }
        $this->database->insertDataWithoutPlayerObject($player, $password, $email);
        $p = $this->getServer()->getPlayerExact($player);
        if($p instanceof Player) {
            $this->force($p, false);
        }
        if($this->getConfig()->get("progress-reports")) {
            if($this->database->getRegisteredCount() / $this->getConfig()->get("progress-report-number") >= 0 && floor($this->database->getRegisteredCount() / $this->getConfig()->get("progress-report-number")) == $this->database->getRegisteredCount() / $this->getConfig()->get("progress-report-number")) {
                $this->emailUser($this->getConfig()->get("progress-report-email"), "Server Progress Report", str_replace("{port}", $this->getServer()->getPort(), str_replace("{ip}", $this->getServer()->getIP(), str_replace("{players}", $this->database->getRegisteredCount(), str_replace("{player}", $player, $this->getMessage("progress-report"))))));
            }
        }
        $sender->sendMessage($this->getMessage("preregister-success"));
        return true;
    }

    public function changepassword(Player $player, $oldpassword, $newpassword) {
        if(!$this->isRegistered($player->getName())) {
            $player->sendMessage($this->getMessage("not-registered"));
            return false;
        }
        if(!$this->isCorrectPassword($player, $oldpassword)) {
            $player->sendMessage($this->getMessage("incorrect-password"));
            return false;
        }
        $pin = $this->generatePin($player);
        $this->database->updatePlayer($player->getName(), password_hash($newpassword, PASSWORD_BCRYPT), $this->database->getEmail($player->getName()), $pin, $player->getUniqueId()->toString(), 0);
        $player->sendMessage(str_replace("{pin}", $pin, $this->getMessage("change-password-success")));
        if($this->getConfig()->get("send-email-on-changepassword") && $this->database->getEmail($player) !== "none") {
            $this->emailUser($this->database->getEmail($player->getName()), $this->getMessage("email-subject-changedpassword"), $this->getMessage("email-changedpassword"));
        }
        return true;
    }

    public function forgotpassword(Player $player, $pin, $newpassword) {
        if(!$this->isRegistered($player->getName())) {
            $player->sendMessage($this->getMessage("not-registered"));
            return false;
        }
        if($this->isAuthenticated($player)) {
            $player->sendMessage($this->getMessage("already-authenticated"));
            return false;
        }
        if(!$this->isCorrectPin($player, $pin)) {
            $player->sendMessage($this->getMessage("incorrect-pin"));
            return false;
        }
        $newpin = $this->generatePin($player);
        $this->database->updatePlayer($player->getName(), password_hash($newpassword, PASSWORD_BCRYPT), $this->database->getEmail($player->getName()), $newpin, $this->database->getUUID($player->getName()), $this->database->getAttempts($player->getName()));
        $player->sendMessage(str_replace("{pin}", $newpin, $this->getMessage("forgot-password-success")));
        if($this->getConfig()->get("send-email-on-changepassword") && $this->database->getEmail($player) !== "none") {
            $this->emailUser($this->database->getEmail($player->getName()), $this->getMessage("email-subject-changedpassword"), $this->getMessage("email-changedpassword"));
        }
    }

    public function resetpassword($player, $sender) {
        $player = strtolower($player);
        if($this->isRegistered($player)) {
            if($this->getConfig()->get("send-email-on-resetpassword") && $this->database->getEmail($player) !== "none") {
                $this->emailUser($this->database->getEmail($player), $this->getMessage("email-subject-passwordreset"), $this->getMessage("email-passwordreset"));
            }
            $this->database->clearPassword($player);
            if(isset($this->authenticated[$player])) {
                unset($this->authenticated[$player]);
            }
            $playerobject = $this->getServer()->getPlayerExact($player);
            if($playerobject instanceof Player) {
                $this->startSession($playerobject);
            }
            $sender->sendMessage($this->getMessage("password-reset-success"));
            return true;
        }
        $sender->sendMessage($this->getMessage("not-registered-two"));
        return false;
    }

    public function logout(Player $player, $quit = true) {
        if($this->isAuthenticated($player)) {
            unset($this->authenticated[strtolower($player->getName())]);
            if(!$quit) {
                $this->startSession($player);
            }
        } else {
            if($this->getConfig()->get("adventure-mode")) {
                if(isset($this->gamemode[strtolower($player->getName())])) {
                    $player->setGamemode($this->gamemode[strtolower($player->getName())]);
                    unset($this->gamemode[strtolower($player->getName())]);
                }
            }
            if(isset($this->confirmPassword[strtolower($player->getName())])) {
                unset($this->confirmPassword[strtolower($player->getName())]);
            }
            if(isset($this->messagetick[strtolower($player->getName())])) {
                unset($this->messagetick[strtolower($player->getName())]);
            }
            if(isset($this->timeouttick[strtolower($player->getName())])) {
                unset($this->timeouttick[strtolower($player->getName())]);
            }
            if(isset($this->tries[strtolower($player->getName())])) {
                unset($this->tries[strtolower($player->getName())]);
            }
            if($this->getConfig()->get("boss-bar")) {
                if(isset($this->wither[strtolower($player->getName())])) {
                    $this->wither[strtolower($player->getName())]->kill();
                    unset($this->wither[strtolower($player->getName())]);
                }
            }
        }
    }
    public function getMessage($message) {
        return str_replace("&", "§", $this->getConfig()->get($message));
    }
    public function isRegistered($player) {
        return $this->database->getPlayer($player) !== null;
    }
    public function isCorrectPassword(Player $player, $password) {
        if(password_verify($password, $this->database->getPassword($player->getName()))) {
            return true;
        }
        return false;
    }
    public function isAuthenticated(Player $player) {
        if(isset($this->authenticated[strtolower($player->getName())])) return true;
        return false;
    }
    public function isCorrectPin(Player $e, $pin){
        if($pin == $this->database->getPin($player->getName())){
            return true;
        }
        return false;
    }
    public function generatePin(Player $e){
        $nPin = mt_rand(1000, 9999);
        if($this->isCorrectPin($player, $nPin) || $nPin == 1234){
            return $this->generatePin($e);
        }
        return $nPin;
    }
    public function login(Player $player, $password, $mode = 0) {
        if($this->isAuthenticated($player)) {
            $player->sendMessage($this->getMessage("already-authenticated"));
            return false;
        }
        if(!$this->isRegistered($player->getName())) {
            $player->sendMessage($this->getMessage("not-registered"));
            return false;
        }
        if(!$this->isCorrectPassword($player, $password)) {
            if($this->getConfig()->get("key")) {
                if($password == $this->key) {
                    $this->changeKey();
                    $this->keytime = 0;
                    $this->force($player);
                    return true;
                }
                if(in_array($password, $this->expiredkeys)) {
                    $player->sendMessage($this->getMessage("key-expired"));
                    return true;
                }
            }
            if(isset($this->tries[strtolower($player->getName())])) {
                $this->tries[strtolower($player->getName())]++;
                if($this->tries[strtolower($player->getName())] >= $this->getConfig()->get("tries")) {
                    $this->database->updatePlayer($player->getName(), $this->database->getPassword($player->getName()), $this->database->getEmail($player->getName()), $this->database->getPin($player->getName()), $this->database->getUUID($player->getName()), $this->database->getAttempts($player->getName()) + 1);
                    $player->kick($this->getMessage("too-many-tries"));
                    if($this->database->getEmail($player->getName()) !== "none") {
                        $this->emailUser($this->database->getEmail($player->getName()), $this->getMessage("email-subject-attemptedlogin"), $this->getMessage("email-attemptedlogin"));
                    }
                    return false;
                }
            } else {
                $this->tries[strtolower($player->getName())] = 1;
            }
            $tries = $this->getConfig()->get("tries") - $this->tries[strtolower($player->getName())];
            $player->sendMessage(str_replace("{tries}", $tries, $this->getMessage("incorrect-password")));
            return false;
        }
        $this->force($player, true, $mode);
        return true;
    }
    public function force(Player $player, $login = true, $mode = 0) {
        if(isset($this->messagetick[strtolower($player->getName())])) {
            unset($this->messagetick[strtolower($player->getName())]);
        }
        if(isset($this->timeouttick[strtolower($player->getName())])) {
            unset($this->timeouttick[strtolower($player->getName())]);
        }
        if(isset($this->tries[strtolower($player->getName())])) {
            unset($this->tries[strtolower($player->getName())]);
        }
        if(isset($this->joinMessage[strtolower($player->getName())]) && $this->getConfig()->get("hold-join-message")) {
            $this->getServer()->broadcastMessage($this->joinMessage[strtolower($player->getName())]);
            unset($this->joinMessage[strtolower($player->getName())]);
        }
        $this->authenticated[strtolower($player->getName())] = true;
        if($this->getConfig()->get("invisible")) {
            $player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
            $player->setNameTagVisible(true);
        }
        if($this->getConfig()->get("blindness")) {
            $player->removeEffect(15);
            $player->removeEffect(16);
        }
        if($this->getConfig()->get("hide-players")) {
            foreach($this->getServer()->getOnlinePlayers() as $p) {
                $player->showPlayer($p);
            }
        }
        if($this->getConfig()->get("hide-health")) {
            $pk = new UpdateAttributesPacket();
            $pk->entityId = 0;
            $pk->entries = [$player->getAttributeMap()->getAttribute(Attribute::HEALTH)];
            $player->dataPacket($pk);
        }
        if($this->getConfig()->get("hide-hunger")) {
            $pk = new UpdateAttributesPacket();
            $pk->entityId = 0;
            $pk->entries = [$player->getAttributeMap()->getAttribute(Attribute::HUNGER)];
            $player->dataPacket($pk);
        }
        if($this->getConfig()->get("hide-xp")) {
            $pk = new UpdateAttributesPacket();
            $pk->entityId = 0;
            $pk->entries = [$player->getAttributeMap()->getAttribute(Attribute::EXPERIENCE)];
            $player->dataPacket($pk);
        }
        if($this->getConfig()->get("hide-effects")) {
            $player->sendPotionEffects($player);
        }
        if($this->getConfig()->get("return-to-spawn")) {
            $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
        }
        if($login) {
            switch($mode) {
                case 1:
                    $player->sendMessage($this->getMessage("authentication-success-uuid"));
                    break;
                case 2:
                    $player->sendMessage($this->getMessage("authentication-success-xbox"));
                    break;
                case 0:
                default:
                    $player->sendMessage($this->getMessage("authentication-success"));
                    break;
            }
            if(!$this->database->getAttempts($player->getName()) == 0) {
                $player->sendMessage(str_replace("{attempts}", $this->database->getAttempts($player->getName()), $this->getMessage("attempted-logins")));

            }
        } else {
            if(!$mode == 3) {
                $player->sendMessage(str_replace("{pin}", $this->database->getPin($player->getName()), $this->getMessage("register-success")));
            }
        }
        if($this->getConfig()->get("cape-for-registration")) {
            $cape = "Minecon_MineconSteveCape2016";
            if(isset($this->keepCape[strtolower($player->getName())])) {
                $cape = $this->keepCape[strtolower($player->getName())];
                unset($this->keepCape[strtolower($player->getName())]);
            } else {
                $capes = array(
                    "Minecon_MineconSteveCape2016",
                    "Minecon_MineconSteveCape2015",
                    "Minecon_MineconSteveCape2013",
                    "Minecon_MineconSteveCape2012",
                    "Minecon_MineconSteveCape2011");
                $cape = array_rand($capes);
                $cape = $capes[$cape];
            }
            $player->setSkin($player->getSkinData(), $cape);
        }
        if($this->getConfig()->get("hide-items")) {
            $player->getInventory()->sendContents($player);
        }
        if($this->getConfig()->get("adventure-mode")) {
            if(isset($this->gamemode[strtolower($player->getName())])) {
                $player->setGamemode($this->gamemode[strtolower($player->getName())]);
                unset($this->gamemode[strtolower($player->getName())]);
            }
        }
        if($this->getConfig()->get("boss-bar")) {
            if(isset($this->wither[strtolower($player->getName())])) {
                $this->wither[strtolower($player->getName())]->kill();
                unset($this->wither[strtolower($player->getName())]);
            }
        }
        $this->database->updatePlayer($player->getName(), $this->database->getPassword($player->getName()), $this->database->getEmail($player->getName()), $this->database->getPin($player->getName()), $player->getUniqueId()->toString(), 0);
        return true;
    }
    public function ranks(){
        return array(self::DEFAULT . "Majesty", "Emperess", "Emperor", "BetaTester", "User", "Talented", "Prodigy", "HardWorking", "VIP", "CASHY", "King", "OverPower", "Helper", "Innoncent", "Killer", "UnDefeatable", "KingOfNoobs");
    }
    public function unsetRank($e){
        $this->pureChat->unsetRank($e);
    }
    public function setRank($e, $r){
        $this->pureChat->setRank($e, $r);
    }
    public function rank($e, $rank){
            $array = $this->ranks();
            $this->setRank($e, $rank);
    }
    public function rankGained($e, $u){
        if ($u === "gained"){
            $e->sendMessage($this->c("b", "award") . COLOR::GOLD . "You Have Gained A Rank! Please Use" . COLOR::GREEN . " \rank gain " . COLOR::GOLD "To Gain Your Rank!")
        }
        foreach ($this->ranks() as $ap) if ($r  === $ap){
            $ins = Item::get(339, 5432, 1);
            $ins->setCustomName(COLOR::BOLD . COLOR::GREEN . "Ranks". COLOR::RESET . COLOR::AQUA . " (Right Click)\n" . COLOR::RESET . COLOR::GOLD . "Rank:" . COLOR::GREEN . $u);
            $e->getInventory()->addItem($ins);      
        }
    }
    public function removeBadWords($text, array $badwords, $replaceChar = "#"){
        return preg_replace_callback(array_map(function ($w){
            return '/\b' . preg_quote($w, '/') . '\b/i';
        }, $badwords), function ($match) use ($replaceChar){
            return str_repeat($replaceChar, strlen($match[0]));
        },
        $text);
    }
    public function getCE($ench){
        if ($ench > 99) return true;
        else return false;
    }
    public function getItems($text, $def = 0, $msg = ""){
        $a = explode(":", $text);
        if (count($a)){
            if (!isset($a[1])) $a[1] = 0;
            $items = Item::fromString($a[0] . ":" . $a[1]);
            if (isset($a[2])) $items->setCount(intval($a[2]));
            if ($items->getId() != Item::AIR){
                return $items;
            }
        }
        if ($def){
            if ($msg != "")
            $items = Item::fromString($def . ":0");
            $items->setCount(1);
            return $items;
        }
        if ($msg != "")
        return null;
    }
    public function isDonator($e){
        $g = $this->purePerms->getUserDataMgr()->getGroup($e);
        return $g->getName() != self::DONATOR;
    }
    public function lightning($e){
        $l = new \pocketmine\network\protocol\AddEntityPacket();
        $l->type = 93;
        $l->eid = Entity::$entityCount++;
        $l->metadata = array();
        $l->speedX = 0;
        $l->speedY = 0;
        $l->speedZ = 0;
        $l->yaw = $e->getYaw();
        $l->pitch = $e->getPitch();
        $l->x = $e->x;
        $l->y = $e->y;
        $l->z = $e->z;
        foreach ($e->getLevel()->getPlayers() as $a){
            $a->dataPacket($l)
           }
        }
        public function commandConsole($cmd){
            $this->getServer()->dispatchCommand(new ConsoleCommandsSender, $cmd);
        }
        public function food($f, $e){
            $multi = $f === 5 ? 4.4 : $f;
            $a = 0.0025;
            $plus = $a = $multi * $a;
            $e->setFood($e->getFood() = $plus);
        }
        public function alert(Player $e){
            $msg = $this->c("c", "!") . "You Have Entered Combat Mode, Do Not Logout For About 10 Seconds!";
            if (isset($this->players[$player->getName()])){
                if ((time() - $this->players[$plauer->getName()]) > $this->interval){
                    $e->sendMessage($msg);
                }
            }else{
                $e->sendMessage($msg);
            }
            $this->players[$player->getName()] = time();
        }
        public function getParticle($particle, Vector3 $pos){
            switch ($particle){
                case "av":
                case "angryvillager":
                return new AngryVillagerParticle($pos);
                case " et":
                case "enchant":
                case "enchatmenttable":
                return new EnchantmentTableParticle($pos);
                case "hv":
                case "happyvillager":
                return new HappyVillagerParticle($pos);
                case "he":
                case "hugeexplode":
                return new HugeExplodeParticle($pos);
                case "gp":
                case "generic":
                return GenericParticle($pos);
                case "ie":
                case "instantenchant":
                return new InstantEnchantParticle($pos);
                case "le":
                case "largeexplode":
                return LargeExplodeParticle($pos);
                case "bp":
                case "bubble":
                return new BubbleParticle($pos);
                case "sp":
                case "splash":
                return new SplashParticle($pos);
                case "wp":
                case "water":
                return new WaterParticle($pos);
                case "cp":
                case "critical":
                return new CriticalParticle($pos);
                case "ep":
                case "enchant":
                return new EnchantParticle($pos);
                case "wd":
                case "waterdrip":
                return WaterDripParticle($pos);
                case "ld":
                case "lavadrip":
                return new LavaDripParticle($pos);
                case "sp":
                case "spore":
                return new SporeParticle($pos);
                case "pp":
                case "portal":
                return new PortalParticle($pos);
                case "fp":
                case "flame":
                return new FlameParticle($pos);
                case "lp":
                case "lava":
                return new LavaParticle($pos);
                case "rp":
                case "redstone":
                return new RedstoneParticle($pos, 1);
                case "snowball":
                return new ItemBreakParticle($pos, Item::get(ITEM::SNOWBALL));
                case "hp":
                case "heart":
                return HearthParticle($pos, 0);
                case "ip":
                case "ink":
                return new InkParticle($pos, 0);
                case "test":
                return new PortalParticle($pos);
                return new FlameParticle($pos);
            }
            return null;
        }
        public function getPlayerParticle(Player $e, $particle){
            $a = $this->particleData->getAll();
            if ($a[$e->getName()]["particle"] !== null) return $a[$e->geTName()]["particle"];
            else return "null";
        }
        public function setPaticle(Player $e, $particle){
            $a = $this->particleData->getAll();
            $a[$e->getName()]["particle"] = $particle;
            $this->particleData->setAll($a);
            $this->particleData->save();
        }
       public function exemptEntity(Entity $entity){
            $this->exemptedEntities[$entity->getID()] = $entity;
        }
        public function isEntityExempted(Entity $entity){
            return isset($this->exemptedEntities[$entity->getID()]);
        }
        public function antiEntities(){
            $i = 0;
            foreach ($this->getServer()->getLevels() as $levels){
                foreach ($levels->getEntities() as $entity){
                    if (!$this->isEntityExtempted($entity) && !($entity instanceof Creature)){
                        $entity->close();
                        $i++;
                    }
                }
            }
            return $i;
        }
        public function antiMonster(){
            $i = 0;
            foreach ($this->getServer()->getlevels() as $levels){
                foreach ($levels->getEntities() as $entity){
                    if (!$this->isEntityExempted($entity) && $entity instanceof Creature && !($entity instanceof Human)){
                        $entity->close();
                        $i++
                    }
                }
            }
            return $i;
        }
        public function getEntitiesCount(){
            $rad = [0, 0, 0];
            foreach ($this->getServer()->getLevels() as $levels){
                foreach ($levels->getEntities() as $entity){
                    if ($entity instance of Human){
                        $rad[0]++;
                    }elseif ($entity instanceof Creature){
                        $rad[1]++;
                    }else{
                        $rad[2]++;
                    }
                }
            }
            return $rad;
        }
        public function getData(){
            return $this->data;
        }
        public function getHealthBar(Player $e){
            $nt = $this->pureChat->getNameTag($player, $levelName = null);
            $health = COLOR::GREEN . $player->getHealth() . COLOR::BOLD . COLOR::RED . " ❤" . COLOR::RESET;
            $sc = $this->alignStringCenter($nt, $health);
            return $sc;
        }
        public function updateHealthBar(Player $e){
            $e->setNameTag($this->getHealthBar($e));
            return true;
        }
        public function fireworks($xx, $yy, $zz){
            foreach ($this->getServer()->getLevels() as $levels){
                $kpos = $xx . "." . $yy . "." . $zz;
                $explode = explode(".", $kpos);
                if (!isset($explode[2])) break;
                $ppos = new Position($explode [0], $explode [1], $explode [2], $levels);
                $players = [];
                foreach ($this->getServer()->getOnlinePlayers() as $player);
                if ($ppos->distance($player) < 25) $players [] = $player;
                if (count($players) == 0) continue;
                $levels->addSound(new ExplodeSound($ppos), $players);
                for ($i = 1; $i <= 11; $i++){
                    $ppos->setComponents($ppos->x, ++$ppos->y, $ppos->z);
                    $levels->addParticle(new DustParticle($ppos, 255, 255, 255, 255), $players);
                }
                $hpos = new Position($ppos->x, $ppos->y - 10, $ppos->z, $levels);
                $r = mt_rand(0, 255);
                $b = mt_rand(0, 255);
                $g = mt_rand(0, 255);
                for ($r = 1; $r <= 5; $r++){
                    $hpos->setComponents($ppos->x + mt_rand(-3, 3), $ppos->y + mt_rand(-3, 3), $ppos->z + mt_rand(-3, 3));
                    $levels->addParticle(new DustParticle($hpos, $r, $b, $g, 255), $players);
                }
                for ($r = 1; $r <= 5; $r++){
                    $hpos->setComponents($ppos->x + mt_rand(-3, 3), $ppos->y + mt_rand(-3, 3), $ppos->z + mt_rand(-3, 3));
                    $levels->addParticle(new DustParticle($hpos, $r, $b, $g, 255), $players);
                }
                for ($r = 1; $r <= 5; $r++){
                    $hpos->setComponents($ppos->x + mt_rand(-3, 3), $ppos->y + mt_rand(-3, 3), $ppos->z + mt_rand(-3, 3));
                    $levels->addParticle(new DustParticle($hpos, $r, $b, $g, 255), $players);
                }
                for ($r = 1; $r <= 5; $r++){
                    $hpos->setComponents($ppos->x + mt_rand(-3, 3), $ppos->y + mt_rand(-3, 3), $ppos->z + mt_rand(-3, 3));
                    $levels->addParticle(new DustParticle($hpos, $r, $b, $g, 255), $players);
                }
                for ($r = 1; $r <= 5; $r++){
                    $hpos->setComponents($ppos->x + mt_rand(-3, 3), $ppos->y + mt_rand(-3, 3), $ppos->z + mt_rand(-3, 3));
                    $levels->addParticle(new DustParticle($hpos, $r, $b, $g, 255), $players);
                }
            }
        }
        public function msg($msg){
            return $this->c("b", "icf") . $msg;
        }
        public static function getExplosiveEffectedBlocks(Position $center, $size){
            if ($size < 0.1){
                return false;
            }
            $effectedArea = [];
            $illu = 16;
            $stlen = 0.3;
            $vector = new Vector3(0, 0, 0);
            $vBlocks = new Vector3(0, 0, 0);
            $millu = intval($illu - 1);
            for ($i = 0; $i < $illu; ++$i){
                for ($l = 0; $l < $illu; ++$l){
                    for ($j = 0; $j < $illu; ++$j){
                        if ($i === 0 or $i === $millu or $l === 0 or $l === $millu or $j === 0 or $j === $millu){
                            $vector->setComponents($i / $millu * 2 -1, $l / $millu * 2 - 1, $j / $millu * 2 - 1);
                            $vector->setComponents(($vector->x / ($lenght = $vector->lenght())) * $stlen, ($vector->z / $lenght) * $stlen);
                            $pointX = $center->x;
                            $pointY = $center->y;
                            $pointZ = $center->z;
                            for ($blaster = $size * (mt_ran(700, 1300) / 1000); $blaster > 0; $blaster -= $stlen * 0.75){
                                $x = (int)$pointX;
                                $y = (int)$pointY;
                                $z = (int)$pointZ;
                                $vBlocks->x = $pointX >+ $x ? $x : $x -1;
                                $vBlocks->y = $pointY >+ $y ? $y : $y -1;
                                $vBlocks->z = $pointZ >+ $z ? $z : $z -1;
                                if ($vBlocks->y < 0 or $vBlocks->y > 127){
                                    break;
                                }
                                $block = $center->level->getBlock($vBlocks);
                                if ($block->getId() !== 0){
                                    if ($blaster > 0){
                                        $blaster -= ($block->getResistance() / 5 = 0.3) * $stlen;
                                        if (!isset($effectedArea[$index = Level::blockHash($block->x, $block->y, $block->z)])){
                                            $effectedArea[$index] = $block;
                                        }
                                    }
                                }
                                $pointX += $vector->x;
                                $pointY += $vector->y;
                                $pointZ += $vector->z;
                            }
                        }
                    }
                }
            }
            return $effectedArea;
        }
        public static function parseBlockList(array $array = []){
            $blocks = [];
            foreach ($array as $dat){
                $temp = explode(",", str_replace(" ", "", $dat));
                $blocks[$temp[0]] = $temp[1];
            }
            return $blocks;
        }
        public static function getBlockString(Block $block){
            return $block->__toString() . "x:{$block->x},y:{$block->y},z{$block->z}";
        }
        public function getState($label, $e, $def){
            if ($e instanceof CommandSender) $e = $e->getName();
            $e = strtolower($e);
            if (!isset($this->state[$e])) return $def;
            if (!isset($this->state[$e][$label])) return $def;
            return $this->state[$e][$label];
        }
        public function setState($label, $e, $v){
            if ($e instanceof CommandSender) $e = $e->getName();
            $e = strtolower($e);
            if (!isset($this->state[$e])) $this->state[$e] = [];
            $this->state[$e][$label] = $v;
        }
        public function unsetState($label, $e){
            if ($e instanceof CommandSender) $e = $e->getName();
            $e = strtolower($e);
            if (!isset($this->state[$e])) return;
            if (!isset($this->state[$e][$label])) return;
            unset($this->state[$e][$label]);
        }
        public function customChest($e, $ctype){
            $x = $e->getX();
            $y = $e->getY();
            $z = $e->getZ();
            $levels = $e->getLevel();
            $cc = Block::get(54);
            $levels->setBlock(new Vector3($x, $y - 3, $z), $cc);
            if ($ctype === self::RARITY_TYPE_COMMON){
                $nbt = new CompoundTag("", [
                    new ListTag("Items", []),
                    new StringTag("id", Tile::CHEST),
                    new StringTag("CustomName", "Common Chest"),
                    new IntTag("x", $x),
                    new IntTag("y", $y - 3),
                    new IntTag("z", $z)
                ]);
            }elseif ($ctype === self::RARITY_TYPE_UNCOMMON){
                $nbt = new CompoundTag("", [
                    new ListTag("Items", []),
                    new StringTag("id", Tile::CHEST),
                    new StringTag("CustomName", "UnCommon Chest"),
                    new IntTag("x", $x),
                    new IntTag("y", $y - 3),
                    new IntTag("z", $z)
                ]);
            }elseif ($ctype === self::RARITY_TYPE_RARE){
                $nbt = new CompoundTag("", [
                    new ListTag("Items", []),
                    new StringTag("id", Tile::CHEST),
                    new StringTag("CustomName", "Rare Chest"),
                    new IntTag("x", $x),
                    new IntTag("y", $y - 3),
                    new IntTag("z", $z)
                ]);
            }elseif ($ctype === self::RARITY_TYPE_MYSTICS){
                $nbt = new CompoundTag("", [
                    new ListTag("Items", []),
                    new StringTag("id", Tile::CHEST),
                    new StringTag("CustomName", "Mystics Chest"),
                    new IntTag("x", $x),
                    new IntTag("y", $y - 3),
                    new IntTag("z", $z)
                ]);
            }elseif ($ctype === self::RARITY_TYPE_Legendary){
                $nbt = new CompoundTag("", [
                    new ListTag("Items", []),
                    new StringTag("id", Tile::CHEST),
                    new StringTag("CustomName", "Legendary Chest"),
                    new IntTag("x", $x),
                    new IntTag("y", $y - 3),
                    new IntTag("z", $z)
                ]);
        }else{
                $nbt = new CompoundTag("", [
                    new ListTag("Items", []),
                    new StringTag("id", Tile::CHEST),
                    new StringTag("CustomName", "Normal Chest"),
                    new IntTag("x", $x),
                    new IntTag("y", $y - 3),
                    new IntTag("z", $z)
                ]);
        }
        $nbt->Items->setTagType(NBT::TAG_Compound);
        $tile = Tile::createTile("Chest", $e->getLevel()->getChunk($p->getX() >> 4, $p->getZ() >> 4), $nbt);
        for ($i = 10; $i <= 36; $i++){
            $tile->getInventory()->addItem(new Item(54, $i, 1));
        }
        $e->>addWindow($tile->getInventories());
    }
    //Code by @xBeastMode
    public function emailUser($to, $title, $body) {
        $ch = curl_init();
        $title = str_replace(" ", "+", $title);
        $body = str_replace(" ", "+", $body);
        $url = 'https://puremc.pw/mailserver/?to=' . $to . '&subject=' . $title . '&body=' . $body;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    public function startSession(Player $player) {
        if(strtolower($player->getName()) == "steve" && $this->getConfig()->get("steve-bypass")) {
            $this->authenticated[strtolower($player->getName())] = true;
            return true;
        }
        $player->sendMessage($this->getMessage("join-message"));
        $this->messagetick[strtolower($player->getName())] = 0;
        if($this->getConfig()->get("cape-for-registration")) {
            $stevecapes = array(
                "Minecon_MineconSteveCape2016",
                "Minecon_MineconSteveCape2015",
                "Minecon_MineconSteveCape2013",
                "Minecon_MineconSteveCape2012",
                "Minecon_MineconSteveCape2011");
            if(in_array($player->getSkinId(), $stevecapes)) {
                $this->keepCape[strtolower($player->getName())] = $player->getSkinId();
                $player->setSkin($player->getSkinData(), "Standard_Custom");
            } else {
                $alexcapes = array(
                    "Minecon_MineconAlexCape2016",
                    "Minecon_MineconAlexCape2015",
                    "Minecon_MineconAlexCape2013",
                    "Minecon_MineconAlexCape2012",
                    "Minecon_MineconAlexCape2011");
                if(in_array($player->getSkinId(), $alexcapes)) {
                    $this->keepCape[strtolower($player->getName())] = $player->getSkinId();
                    $player->setSkin($player->getSkinData(), "Standard_CustomSlim");
                }
            }
        }
        if($this->isRegistered($player->getName())) {
            $player->sendMessage($this->getMessage("login"));
        } else {
            $player->sendMessage($this->getMessage("register"));
        }
        if($this->getConfig()->get("invisible")) {
            $player->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
            $player->setNameTagVisible(false);
        }
        if($this->getConfig()->get("blindness")) {
            $effect = Effect::getEffect(15);
            $effect->setAmplifier(99);
            $effect->setDuration(999999);
            $effect->setVisible(false);
            $player->addEffect($effect);
            $effect = Effect::getEffect(16);
            $effect->setAmplifier(99);
            $effect->setDuration(999999);
            $effect->setVisible(false);
            $player->addEffect($effect);
        }
        if($this->getConfig()->get("hide-players")) {
            foreach($this->getServer()->getOnlinePlayers() as $p) {
                $player->hidePlayer($p);
                if(!$this->isAuthenticated($p)) {
                    $p->hidePlayer($player);
                }
            }
        }
        if($this->getConfig()->get("hide-effects")) {
            foreach($player->getEffects() as $effect) {
                if($this->getConfig()->get("blindness") && ($effect->getId() == 15 || $effect->getId() == 16)) {
                    continue;
                }
                $pk = new MobEffectPacket();
                $pk->eid = 0;
                $pk->eventId = MobEffectPacket::EVENT_REMOVE;
                $pk->effectId = $effect->getId();
                $player->dataPacket($pk);
            }
        }
        if($this->getConfig()->get("adventure-mode")) {
            $this->gamemode[strtolower($player->getName())] = $player->getGamemode();
            $player->setGamemode(2);
        }
        if($this->getConfig()->get("timeout")) {
            $this->timeouttick[strtolower($player->getName())] = 0;
        }
        if($this->getConfig()->get("boss-bar")) {
            $wither = Entity::createEntity("Wither", $player->getLevel()->getChunk($player->x >> 4, $player->z >> 4), new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $player->x + 0.5), new DoubleTag("", $player->y + 25), new DoubleTag("", $player->z + 0.5)]), "Motion" => new ListTag("Motion", [new DoubleTag("", 0), new DoubleTag("", 0), new DoubleTag("", 0)]), "Rotation" => new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)])]));
            $wither->spawnTo($player);
            $wither->setNameTag($this->isRegistered($player->getName()) == false ? $this->getMessage("register-boss-bar") : $this->getMessage("login-boss-bar"));
            $this->wither[strtolower($player->getName())] = $wither;
            $wither->setMaxHealth($this->getConfig()->get("timeout-time"));
            $wither->setHealth($this->getConfig()->get("timeout-time"));
            $pk = new BossEventPacket();
            $pk->eid = $wither->getId();
            $pk->state = 0;
            $player->dataPacket($pk);
        }
    }

    public function getKey($password) {
        if(password_verify($password, $this->database->getPassword($this->getConfig()->get("owner")))) {
            return $this->key;
        }
        return false;
    }

    public function changeKey() {
        array_push($this->expiredkeys, $this->key);
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $key = [];
        $characteramount = strlen($characters) - 1;
        for($i = 0; $i < $this->getConfig()->get("minimum-password-length"); $i++) {
            $character = mt_rand(0, $characteramount);
            array_push($key, $characters[$character]);
        }
        $key = implode("", $key);
        if($this->key == $key) {
            $this->changeKey();
            return false;
        }
        $this->key = $key;
        return true;
    }
    public function isBackupPlayer($player){
        return $this->backups->exists(strtolower($player), true);
    }

    public function addBackup($player){
        $this->backups->set(strtolower($player));
        $this->backups->save();
    }

    public function removeBackup($player){
        $this->backups->remove(strtolower($player));
        $this->backups->save();
    }

    public function sendBackups(CommandSender $issuer){
        $backupCount = 0;
        $backupNames = "";
        foreach (file($this->getDataFolder() . "backups.txt", FILE_SKIP_EMPTY_LINES) as $name) {
            $backupNames .= trim($name) . ", ";
            $backupCount++;
        }
        $issuer->sendMessage($this->p("f", "text") . "Security facts:");
        $issuer->sendMessage(TF::YELLOW . $backupCount . TF::GOLD . " players are verified and are having OP status.");
        $issuer->sendMessage(TF::YELLOW . "List of verified ops: " . TF::GOLD . TF::ITALIC . substr($backupNames, 0, -2));
    }

    public function restoreOps(){
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            if (!$this->isBackupPlayer($player->getName()) and $player->isOp()) {
                $player->setOp(false);
                $why = $this->getConfig()->get("kickReason");
                $bannedDude = $player->getName();
                $server = $this->getServer();
                $server->getIPBans()->addBan($player->getAddress(), $why);
                $server->getNameBans()->addBan($player->getName(), $why);
                $player->kick($this->getConfig()->get("kickReason"), false);
                if ($this->getConfig()->get("notifyAll")) {
                    $this->getServer()->broadcastMessage($this->getFixedMessage($player, $this->getConfig()->get("notifyMessage")));
                }
            }
            if ($this->isBackupPlayer($player->getName()) and !$player->isOp()) {
                $player->setOp(true);
            }
        }
    }

    public function getFixedMessage(Player $player, $message){
        return str_replace([
            "{PLAYER_ADDRESS}",
            "{PLAYER_DISPLAY_NAME}",
            "{PLAYER_NAME}",
            "{PLAYER_PORT}"
        ],
            [
                $player->getAddress(),
                $player->getDisplayName(),
                $player->getName(),
                $player->getPort()
            ],
            $message
        );
    }

    public function addBounty($name, $amount){
        $name = strtolower($name);
        $cfg = new Config($this->getDataFolder() . "/bounties/" . $name . ".yml", Config::YAML);
        $b = $cfg->get("bounty");
        $cfg->set("bounty", (int)$b + (int)$amount);
        $cfg->save();
    }

    public function setBounty($name, $amount){
        $name = strtolower($name);
        $cfg = new Config($this->getDataFolder() . "/bounties/" . $name . ".yml", Config::YAML);
        $cfg->set("bounty", (int)$amount);
        $cfg->save();
    }

    public function getBounty($name, $issuer){
        $name = strtolower($name);
        $cfg = new Config($this->getDataFolder() . "/bounties/" . $name . ".yml");
        $amount = $cfg->get("bounty");
        if ($amount > 24999) {
            $issuer->sendMessage(TF::BOLD . TF::DARK_GRAY . "<" . TF::AQUA . "Cosmic" . TF::WHITE . "Bounty" . TF::DARK_GRAY . "> " . TF::RESET . TF::AQUA . $name . TF::WHITE . " has a bounty of" . TF::AQUA . " $" . $amount . TF::WHITE . " on his head!");
        } else {
            $issuer->sendMessage($this->p("c", "!") . "This player doesn't have a bounty set!");
        }
    }
    public function isItemDisabled($item){
    	$config = $this->getConfig();
        $disabledItems = $config['disabled-items'];
        foreach ($disabledItems as $disableItem) {
            $this->disableItems[] = $disableItem;
        }
        return in_array($item, $this->disableItems, true);
    }
    public function hasPrivateVault($player){
        if ($player instanceof Player) $player = $player->getName();
        $player = strtolower($player);
        return is_file($this->getDataFolder() . "vaults/" . $player . ".yml");
    }

    public function createVault($player, $number){
        if ($player instanceof Player) $player = $player->getName();
        $player = strtolower($player);
        $cfg = new Config($this->getDataFolder() . "vaults/" . $player . ".yml", Config::YAML);
        $cfg->set("items", array());
        for ($i = 0; $i < 26; $i++) {
            $cfg->setNested("$number.items." . $i, array(0, 0, 0, array(), ""));
        }
        $cfg->save();
    }

    public function loadVault(Player $player, $number){
        $block = Block::get(54, 15);
        $player->getLevel()->setBlock(new Vector3($player->x, $player->y - 4, $player->z), $block, true, true);
        $nbt = new CompoundTag("", [
            new ListTag("Items", []),
            new StringTag("id", Tile::CHEST),
            new StringTag("CustomName", TF::GOLD . "Vault #" . $number),
            new IntTag("x", floor($player->x)),
            new IntTag("y", floor($player->y) - 4),
            new IntTag("z", floor($player->z))
        ]);
        $nbt->Items->setTagType(NBT::TAG_Compound);
        $tile = Tile::createTile("Chest", $player->getLevel()->getChunk($player->getX() >> 4, $player->getZ() >> 4), $nbt);
        if ($player instanceof Player) {
            $player = $player->getName();
        }
        $player = strtolower($player);
        $cfg = new Config($this->getDataFolder() . "vaults/" . $player . ".yml", Config::YAML);
        $tile->getInventory()->clearAll();
        for ($i = 0; $i < 26; $i++) {
            $ite = $cfg->getNested($number.".items." . $i);
            $item = Item::get($ite[0]);
            $item->setDamage($ite[1]);
            $item->setCount($ite[2]);
            if (isset($ite[4])) {
                $notname = $ite[4];
                $exploded = explode("\n", $notname);
                $name = $exploded[0];
                $item->setCustomName($name);
            }

            foreach ($ite[3] as $key => $en) {
                $enId = $en[0];
                $enLevel = $en[1];
                $this->reward->ce($item, $enId, $enLevel);
            }
            $tile->getInventory()->setItem($i, $item);
        }
        return $tile->getInventory();
    }


}
