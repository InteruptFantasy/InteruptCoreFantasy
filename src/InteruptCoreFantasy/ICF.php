<?php

namespace InteruptCoreFantasy;

use pocketmine\entity\{Entity, Attribute, Effect};
use pocketmine\nbt\tag\{CompoundTag, DoubleTag, FloatTag, ListTag};
use pocketmine\network\protocol\{MobEffectPacket, UpdateAttributesPacket};
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\level\particle\{AngryVillagerParticle, BubbleParticle, CriticalParticle, DustParticle, EnchantParticle, EnchantmentTableParticle, ExplodeParticle, FlameParticle, FloatingTextParticle, GenericParticle, HappyVillagerParticle, HeartParticle, HugeExplodeParticle, InkParticle, InstantEnchantParticle, ItemBreakParticle, LargeExplodeParticle, LavaDripParticle, LavaParticle, MobSpawnParticle, Particle, PortalParticle, RedstoneParticle, SmokeParticle, SplashParticle, SporeParticle, TerrainParticle, WaterDripParticle, WaterParticle};
use pocketmine\level\sound\{ExpPickupSound, ExplodeSound};
use pocketmine\utils\{Config, TextFormat as COLOR
};
use pocketmine\entity\{Creature, Effect, Entity, Human, Item as EntityItem, Living
};
use pocketmine\math\Vector3;
use SQLite3;




class ICF \pocketmine\plugin\PluginBase extends \pocketmine\event\Listener {

	const RARITY_TYPE_COMMON = 0;//COMMON RARITY
	const RARITY_TYPE_UNCOMMON = 1;//UNCOMMON RARITY
	const RARITY_TYPE_RARE = 2;//RARE RARITY
	const RARITY_TYPE_MYSTHIC = 3;//MYSTHIC RARITY
    const RARITY_TYPE_LEGENDARY = 4;//LEGENDARY RARITY
    const RARITY_TYPE_ANCIENT = 5;//ANCIENT RARITY
    const RARITY_TYPE_PHANTAMS = 6;//PHANTASM RARITY

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
    public static $index = ["mystics","holy","legends","void","enchanter"];
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
        @mkdir($this->getDataFolder());
        $data = array("translate.yml", "config.yml", "chat.yml", "custom.yml", "text.yml", "vote.yml", "coordinate.yml", "rpg.yml");
        foreach ($data as $file){
            if (!file_exists($this->getDataFolder() . $file)){
                @mkdir($this->getDataFolder());
                file_put_contents($this->getDataFolder() . $file, $this->getResource($file));
            }
        }
        if (!is_dir($this->getDataFolder())) mkdir($this->getDataFolder());
        if (!is_dir($this->getDataFolder() . "players/")) mkdir($this->getDataFolder() . "players/");
        if (!is_dir($this->getDataFolder() . "vaults/")) mkdir($this->getDataFolder() . "vaults/");
        if (!is_dir($this->getDataFolder() . "/bounties")) mkdir($this->getDataFolder() . "/bounties");
        if (!is_dir($this->getDataFolder() . "Lists/")) mkdir($this->getDataFolder() . "Lists/");
        $this->vote = new Config($this->getDataFolder() . "vote.yml", Config::YAML);
        $this->block = new Config($this->getDataFolder() . "blocks.json", Config::JSON);
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
        }
    }
    public function ranks(){
        return array(self::DEFAULT . "BetaTester", "User", "Talented", "Prodigy", "HardWorking", "VIP", "CASHY", "King", "OverPower", "Helper", "Innoncent", "Killer");
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


}
