<?php

namespace LeRUGod\LMerry;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class LMerry extends PluginBase implements Listener {

    protected $data;
    protected $db;

    protected $sy = "§b§l[ §f시스템 §b]§r ";

    public function onEnable() {

        @mkdir($this->getDataFolder());
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        $this->addCommand(["결혼 청혼","결혼 수락","결혼식 시작","결혼 거절","결혼 이혼","결혼식장 설정","결혼식 이동","결혼 정보","결혼식 종료","결혼식 정보"]);
        $this->data = new Config($this->getDataFolder()."merry.yml",Config::YAML);
        $this->db = $this->data->getAll();
        $this->db["결혼식"]["신랑"] = null;
        $this->db["결혼식"]["신부"] = null;
        $this->onsave();

    }

    public function addCommand($arr) {

        $commandMap = $this->getServer()->getCommandMap();
        foreach ($arr as $command) {
            $aaa = new PluginCommand($command, $this);
            $aaa->setDescription('결혼 커맨드');
            $commandMap->register($command, $aaa);
        }

    }

    public function onsave(){

        $this->data->setAll($this->db);
        $this->data->save();

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($sender instanceof Player){

            $name = $sender->getName();

            if ($command->getName() == "결혼 청혼"){

                if (isset($args[0])){

                    $a = $this->getServer()->getPlayer($args[0]);
                    if ($a == null){

                        $sender->sendMessage($this->sy."§l§f제대로 된 사람 이름을 입력해주세요!");
                        return true;

                    }

                    if ($a->isOnline()){

                        $aname = $a->getName();

                        if ($aname == $name){

                            $sender->sendMessage($this->sy."§l§f자기 자신과는 결혼할 수 없습니다!");

                            return true;

                        }

                        if (isset($this->db[$aname]["결혼"])){

                            $sender->sendMessage($this->sy."§l§f이미 결혼한 사람에게는 청혼 할 수 없습니다!");

                            return true;

                        }

                        $this->db[$name]["청혼"] = $aname;
                        $this->onsave();
                        $sender->sendMessage($this->sy."§l§f".$aname." 님에게 결혼을 신청했습니다!");
                        $a->sendMessage($this->sy."§l§f".$name." 님이 당신에게 결혼을 신청하셨습니다! 수락하시려면 /결혼 수락 [ 청혼자 닉 ] 을 입력해주시고 아니라면 /결혼 거절 [ 청혼자 닉 ] 을 입력해주세요!");

                        return true;

                    }else{

                        $sender->sendMessage($this->sy."§l§f대상이 현재 오프라인입니다!");

                        return true;

                    }

                }else{

                    $sender->sendMessage($this->sy."§l§f청혼할 플레이어의 이름을 입력해주세요!");

                    return true;

                }

            }elseif ($command->getName() == "결혼 수락"){

                if (isset($args[0])){

                    $b = $this->getServer()->getPlayer($args[0]);
                    if ($b == null){

                        $sender->sendMessage($this->sy."§l§f제대로 된 사람 이름을 입력해주세요!");
                        return true;

                    }

                    if ($b->isOnline()){

                        $bname = $b->getName();

                        if (isset($this->db[$bname]["결혼"])){

                            $this->db[$bname]["청혼"] = null;
                            $this->db[$this->db[$bname]["결혼"]]["결혼"] = null;
                            $this->db[$this->db[$bname]["결혼"]]["결혼식 여부"] = null;
                            $this->db[$name]["결혼"] = $bname;
                            $this->db[$bname]["결혼"] = $name;
                            $this->db[$name]["결혼식 여부"] = 0;
                            $this->db[$bname]["결혼식 여부"] = 0;
                            $this->onsave();

                            $this->getServer()->broadcastMessage($this->sy."§l§f".$bname." 님이 ".$name." 님과 바람났습니다!");
                            $sender->sendMessage($this->sy."§l§f상대방의 청혼을 수락하셨습니다! /결혼식 시작 으로 결혼식을 열 수 있습니다!");
                            $b->sendMessage($this->sy."§l§f상대방이 당신의 청혼을 수락하였습니다! /결혼식 시작 으로 결혼식을 열 수 있습니다!");

                            return true;

                        }

                        if ($this->db[$bname]["청혼"] = $name){

                            $this->db[$bname]["청혼"] = null;

                            $this->db[$name]["결혼"] = $bname;
                            $this->db[$bname]["결혼"] = $name;
                            $this->db[$name]["결혼식 여부"] = 0;
                            $this->db[$bname]["결혼식 여부"] = 0;
                            $this->onsave();

                            $this->getServer()->broadcastMessage($this->sy."§l§f".$bname." 님의 청혼을 ".$name." 님이 수락하셨습니다!");
                            $sender->sendMessage($this->sy."§l§f상대방의 청혼을 수락하셨습니다! /결혼식 시작 으로 결혼식을 열 수 있습니다!");
                            $b->sendMessage($this->sy."§l§f상대방이 당신의 청혼을 수락하였습니다! /결혼식 시작 으로 결혼식을 열 수 있습니다!");

                            return true;

                        }else{

                            $sender->sendMessage($this->sy."§l§f청혼을 받은 사람에게 수락해야 합니다!");

                            return true;

                        }

                    }else{

                        $sender->sendMessage($this->sy."§l§f대상이 현재 오프라인입니다!");

                        return true;

                    }
                }else{

                    $sender->sendMessage($this->sy."§l§f수락할 플레이어의 이름을 입력해주세요!");

                    return true;

                }

            }elseif ($command->getName() == "결혼식장 설정"){

                if ($sender->isOp()){

                    $x = round($sender->getX());
                    $y = round($sender->getY());
                    $z = round($sender->getZ());

                    $world = $sender->getLevel()->getFolderName();

                    $this->db["결혼식장"]["X"] = $x;
                    $this->db["결혼식장"]["Y"] = $y;
                    $this->db["결혼식장"]["Z"] = $z;

                    $this->db["결혼식장"]["world"] = $world;

                    $this->onsave();

                    $sender->sendMessage($this->sy."§l§f결혼식장 위치 지정이 성공적으로 마무리되었습니다!");
                    $sender->sendMessage($this->sy."§l§f생성된 결혼식장의 위치는 X: ".$x." Y: ".$y." Z: ".$z." World: ".$world." 입니다");

                    return true;

                }else{

                    $sender->sendMessage($this->sy."§l§f이 명령어는 OP만 사용 가능합니다!");
                    return true;

                }

            }elseif ($command->getName() == "결혼식 시작"){

                if (!isset($this->db["결혼식장"])){

                    $sender->sendMessage($this->sy."§l§f결혼식장이 생성되지 않아 이동하실 수 없습니다!");
                    return true;

                }

                if (isset($this->db["결혼식"]["신랑"])){

                    $sender->sendMessage($this->sy."§l§f결혼식이 이미 진행중입니다! 나중에 시도해 주십시오!");
                    return true;

                }


                if (isset($this->db[$name]["결혼"])){

                    if ($this->db[$name]["결혼식 여부"] != 1){

                        $c = $this->getServer()->getPlayer($this->db[$name]["결혼"]);
                        if ($c == null){

                            $sender->sendMessage($this->sy."§l§f제대로 된 사람 이름을 입력해주세요!");
                            return true;

                        }

                        if ($c->isOnline()){

                            $cname = $c->getName();

                            $pos = new Position($this->db["결혼식장"]["X"],$this->db["결혼식장"]["Y"],$this->db["결혼식장"]["Z"],$this->getServer()->getLevelByName($this->db["결혼식장"]["world"]));

                            $c->teleport($pos,$c->getYaw(),$c->getPitch());
                            $sender->teleport($pos,$sender->getYaw(),$sender->getPitch());

                            $this->getServer()->broadcastMessage($this->sy."§l§f".$name." 님과 ".$cname." 님의 결혼식이 시작되었습니다! /결혼식 이동 으로 이동 가능합니다!");
                            $this->db[$cname]["결혼식 여부"] = 1;
                            $this->db[$name]["결혼식 여부"] = 1;
                            $this->db["결혼식"]["신랑"] = $name;
                            $this->db["결혼식"]["신부"] = $cname;

                            $this->onsave();

                            return true;

                        }else{

                            $sender->sendMessage($this->sy."§l§f대상이 현재 오프라인입니다!");

                            return true;

                        }

                    }else{

                        $sender->sendMessage($this->sy."§l§f결혼식을 두번하면 혼나요!");

                        return true;

                    }

                }else{

                    $sender->sendMessage($this->sy."§l§f결혼식은 혼자서 할 수 없습니다!");

                    return true;

                }

            }elseif ($command->getName() == "결혼식 이동"){

                if (!isset($this->db["결혼식장"])){

                    $sender->sendMessage($this->sy."§l§f결혼식장이 생성되지 않아 이동하실 수 없습니다!");
                    return true;

                }

                if (!isset($this->db["결혼식"]["신랑"])){

                    $sender->sendMessage($this->sy."§l§f결혼식 진행중이 아닙니다!");
                    return true;

                }

                if ($name == $this->db["결혼식"]["신랑"] or $name == $this->db["결혼식"]["신부"]){

                    $sender->sendMessage($this->sy."§l§f이미 결혼식장에 있습니다!");
                    return true;

                }

                $pos = new Position($this->db["결혼식장"]["X"],$this->db["결혼식장"]["Y"],$this->db["결혼식장"]["Z"],$this->getServer()->getLevelByName($this->db["결혼식장"]["world"]));

                $sender->teleport($pos,$sender->getYaw(),$sender->getPitch());
                $sender->sendMessage($this->sy."§l§f결혼식장으로 이동하였습니다!");
                $this->getServer()->broadcastMessage($this->sy."§l§f".$name." 님이 결혼식에 참여하셨습니다!");


            }elseif ($command->getName() == "결혼 거절"){

                if (isset($args[0])){

                    $d = $this->getServer()->getPlayer($args[0]);
                    if ($d == null){

                        $sender->sendMessage($this->sy."§l§f제대로 된 사람 이름을 입력해주세요!");
                        return true;

                    }
                    if ($d->isOnline()){

                        $dname = $d->getName();

                        $this->db[$dname]["결혼"] = null;
                        $sender->sendMessage($this->sy."§l§f".$dname." 님의 청혼을 거절했습니다!");
                        $d->sendMessage($this->sy."§l§f".$name." 님이 당신의 청혼을 거절했습니다!");
                        $this->getServer()->broadcastMessage($this->sy."§l§f".$name." 님이 ".$dname." 님의 청혼을 거절했습니다!");

                        return true;

                    }else{

                        $sender->sendMessage($this->sy."§l§f대상이 현재 오프라인입니다!");

                        return true;

                    }

                }else{

                    $sender->sendMessage($this->sy."§l§f거절할 플레이어의 이름을 입력해주세요!");

                }

            }elseif ($command->getName() == "결혼 이혼"){

                if (isset($this->db[$name]["결혼"])){

                    if (isset($args[0])){

                        $e = $this->getServer()->getPlayer($args[0]);

                        if ($e == null){

                            $sender->sendMessage($this->sy."§l§f제대로 된 사람 이름을 입력해주세요!");
                            return true;

                        }

                        if (!$e->isOnline()){

                            $sender->sendMessage($this->sy."§l§f대상이 현재 오프라인입니다!");

                            return true;

                        }

                        $ename = $e->getName();

                        $sender->sendMessage($this->sy."§l§f".$ename." 님과 이혼했습니다!");
                        $e->sendMessage($this->sy."§l§f".$name." 님에게 이혼당했습니다!");
                        $this->getServer()->broadcastMessage($this->sy."§l§f".$name." 님이 ".$ename." 님과 이혼했습니다 !");

                        $this->db[$name]["결혼"] = null;
                        $this->db[$ename]["결혼"] = null;
                        $this->db[$name]["결혼식 여부"] = null;
                        $this->db[$ename]["결혼식 여부"] = null;

                        $this->onsave();

                        return true;

                    }else{

                        $sender->sendMessage($this->sy."§l§f이혼할 플레이어의 이름을 입력해주세요!");

                        return true;

                    }

                }else{

                    $sender->sendMessage($this->sy."§l§f이혼은 혼자 할 수 없습니다!");

                    return true;

                }

            }elseif ($command->getName() == "결혼 정보"){

                if (isset($args[0])){
                    $f = $this->getServer()->getPlayer($args[0]);
                    if ($f == null) {

                        $sender->sendMessage($this->sy . "§l§f제대로 된 사람 이름을 입력해주세요!");
                        return true;
                    }
                    if (!$f->isOnline()){

                        $sender->sendMessage($this->sy."§l§f대상이 현재 오프라인입니다!");
                        return true;

                    }
                    $fname = $f->getName();
                    if (isset($this->db[$fname]["결혼"])){

                        $sender->sendMessage($this->sy."§l§f".$fname." 님은 ".$this->db[$fname]["결혼"]." 님과 결혼하셨습니다!");

                        return true;

                    }else{

                        $sender->sendMessage($this->sy."§l§f".$fname." 님은 결혼하지 않으셨습니다!");

                        return true;

                    }

                }else{

                    $sender->sendMessage($this->sy."§l§f정보를 보고싶은 사람의 이름을 입력해주세요!");

                    return true;

                }

            }elseif ($command->getName() == "결혼 내정보"){

                if (!isset($this->db[$name]["결혼"])){

                    $sender->sendMessage($this->sy."§l§f".$name." 님은 결혼하지 않으셨습니다!");

                    return true;

                }else{

                    $sender->sendMessage($this->sy."§l§f".$name." 님은 ".$this->db[$name]["결혼"]." 님과 결혼하셨습니다!");

                    return true;

                }

            }elseif ($command->getName() == "결혼식 종료"){

                if (!$name == $this->db["결혼식"]["신랑"] and !$name == $this->db["결혼식"]["신부"]){

                    $sender->sendMessage($this->sy."§l§f결혼식 종료는 결혼식 주최자만 할 수 있습니다!");
                    return true;

                }

                $this->getServer()->broadcastMessage($this->sy."§l§f결혼식이 종료 되었습니다! 참석자들은 스폰으로 이동해주세요!");

                $spawn = $this->getServer()->getDefaultLevel()->getSpawnLocation();

                $pl1 = $this->getServer()->getPlayer($this->db["결혼식"]["신랑"]);

                if (!$pl1 == null){

                    if ($pl1->isOnline()){

                        $pl1->teleport($spawn,$pl1->getYaw(),$pl1->getPitch());

                    }

                }

                $pl2 = $this->getServer()->getPlayer($this->db["결혼식"]["신부"]);

                if (!$pl2 == null){

                    if ($pl2->isOnline()){

                        $pl2->teleport($spawn,$pl2->getYaw(),$pl2->getPitch());

                    }

                }
                $this->db["결혼식"]["신랑"] = null;
                $this->db["결혼식"]["신부"] = null;

                $this->onsave();

                return true;

            }elseif ($command->getName() == "결혼식 정보"){

                if (!isset($this->db["결혼식"]["신랑"])){

                    $sender->sendMessage($this->sy."§l§f결혼식이 진행중이 아닙니다!");
                    return true;

                }

                $sender->sendMessage($this->sy."§l§f신랑 : ".$this->db["결혼식"]["신랑"]);
                $sender->sendMessage($this->sy."§l§f신부 : ".$this->db["결혼식"]["신부"]);
                return true;

            }
        }

        return true;

    }

    public function onjoin(PlayerJoinEvent $event){

        $pl = $event->getPlayer();
        $name = $pl->getName();

        if (!isset($this->db[$name]["결혼"])){

            $this->db[$name]["결혼"] = null;
            $this->onsave();

        }
        if (!isset($this->db[$name]["결혼식 여부"])){

            $this->db[$name]["결혼식 여부"] = null;
            $this->onsave();

        }
        if (!isset($this->db[$name]["청혼"])){

            $this->db[$name]["청혼"] = null;
            $this->onsave();

        }
        return true;

    }
}