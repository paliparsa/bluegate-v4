<?php
namespace App\Domain\Provisioning;
use App\Domain\Nodes\Node;

final class ConfigUriBuilder {
 public function build(Node $node, object $inbound, string $uuid, string $email): ?string {
  $raw=is_string($inbound->settings)?json_decode($inbound->settings,true):($inbound->settings??[]);
  $host=$node->metadata['public_host'] ?? parse_url($node->panel_url,PHP_URL_HOST);
  $port=(int)($inbound->port ?: ($raw['port']??0)); if(!$host||!$port) return null;
  $protocol=strtolower($inbound->protocol ?: ($raw['protocol']??''));
  $stream=$raw['streamSettings']??[];
  $network=$stream['network']??'tcp'; $security=$stream['security']??'none';
  $params=['type'=>$network,'security'=>$security];
  $tls=$stream['tlsSettings']??$stream['realitySettings']??[];
  if(!empty($tls['serverName'])) $params['sni']=$tls['serverName'];
  if(!empty($tls['fingerprint'])) $params['fp']=$tls['fingerprint'];
  if(!empty($tls['settings']['serverName'])) $params['sni']=$tls['settings']['serverName'];
  if(!empty($tls['settings']['fingerprint'])) $params['fp']=$tls['settings']['fingerprint'];
  if(!empty($tls['settings']['publicKey'])) $params['pbk']=$tls['settings']['publicKey'];
  if(!empty($tls['settings']['shortIds'][0])) $params['sid']=$tls['settings']['shortIds'][0];
  if($network==='ws'){ $ws=$stream['wsSettings']??[]; if(!empty($ws['path']))$params['path']=$ws['path']; if(!empty($ws['headers']['Host']))$params['host']=$ws['headers']['Host']; }
  if($network==='grpc'){ $g=$stream['grpcSettings']??[]; if(!empty($g['serviceName']))$params['serviceName']=$g['serviceName']; }
  $q=http_build_query($params,'','&',PHP_QUERY_RFC3986); $tag=rawurlencode('BlueGate-'.$email);
  if($protocol==='vless') return "vless://{$uuid}@{$host}:{$port}?{$q}#{$tag}";
  if($protocol==='trojan') return "trojan://{$uuid}@{$host}:{$port}?{$q}#{$tag}";
  if($protocol==='vmess'){
   return 'vmess://'.base64_encode(json_encode(['v'=>'2','ps'=>'BlueGate-'.$email,'add'=>$host,'port'=>(string)$port,'id'=>$uuid,'aid'=>'0','scy'=>'auto','net'=>$network,'type'=>'none','host'=>$params['host']??'','path'=>$params['path']??'','tls'=>$security==='none'?'':$security,'sni'=>$params['sni']??''],JSON_UNESCAPED_SLASHES));
  }
  return null;
 }
}