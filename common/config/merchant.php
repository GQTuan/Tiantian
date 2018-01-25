<?php
/**
1）merchant_private_key，商户私钥;merchant_public_key,商户公钥；商户需要按照《密钥对获取工具说明》操作并获取商户私钥，商户公钥。
2）demo提供的merchant_private_key、merchant_public_key是测试商户号1118004517的商户私钥和商户公钥，请商家自行获取并且替换；
3）使用商户私钥加密时需要调用到openssl_sign函数,需要在php_ini文件里打开php_openssl插件
4）php的商户私钥在格式上要求换行，如下所示；
*/
// const MERCHANT_PRIVATE_KEY = '-----BEGIN PRIVATE KEY-----
// MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAMgQgqjefxd7FAKw
// jCDfTSRR1qNky5lLtWDZFzvqXmWXNgFqZ2k9sdDRWd0a54RpCkqZP7MVs5s2mpjG
// TnLhb0hW+vSFzJ1LPMBrhhgbW/l1swvl6SZpA+STbGRlaj/KI+ty20PYVAvBlqNR
// I6gzyGLsspjE120pR3hMnA3SrAknAgMBAAECgYAIO62011sxky4SokNSAv6AtSF5
// HefHCjePob90BsQVuDBPxJo22YQczNC+6aGHcG8s/LuDWixHAetAyEBoN+DFbbEz
// aT2D0+jR782Tmg8T+ZRroz6shCvRcww8ue4BSv1oRpnkEfZWvvmbCfXljdhfLyjU
// Cj32SHObJxx6v2LuCQJBAO04VHBkEQQ4g9epbqQqjNxpgwcTaRqR5fCBSL5wqnz0
// QnjPpVPgXUpxUO7LgO2ugEiqCyRXUBx7X21THmLLSxUCQQDX5yjqs4dNH/fL0PLH
// Vg9rZzlb6QftsJVJoM6Ppmly5eG8/u5b+h3G8LwSVSl7Q3UlsX4NqJg3neiVqWNA
// o2JLAkBvKaFwFYRf3PfzfONrLMFbnFoZW8A6AjqlbIAhNfy+l1v67xtDZfVxqA5M
// CLM/LjHRW1XjTsMsxaefPH8VvrxNAkAP5uO5na16fb9HkMVA71LFa13rKhe+ZwIn
// 4SM1q2Ea6FmfDPvLZHmq/HItu34JqSdItnD9WoyTlBrcYxR/X0lPAkASmC3E8Fvy
// i80wEPNd/gvmss655lrK2W195E6lfBJw5XmFeAXADyKUO65U4ee+bWB3VabnSKap
// 0sHcaONUV5bD
// -----END PRIVATE KEY-----';
const MERCHANT_PRIVATE_KEY = '-----BEGIN PRIVATE KEY-----
MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAPoHCqqu/UBbUjPLxVQ
m9Ov8SSbXP6jQKXrnhjbGECBRY7MrR/VeI/yax61YGge8TlWOLaBGIgxkz0ZMeR3TyY
LtFQ+VvarO773NIURwr6fuCUk5POB6l7Yiy54QxNsgqBELDlpBZ4U+zZkSiPJXNEzLw
o2bOXTI0NevrMxrNurtAgMBAAECgYAnGMLKPeLMFXudLdgFeLID1Md7k10ZALP7zAho
uD/9TbzRX4s5CyeFcI6YxJYNb5ywNDNeRJk+ZuUikQK/b0arK1a67k867uRb0TJN4Up
4cKsWYVwKLDnbcMdE6ymsruolq26Vhoz3RG+y5mPFrGkcNQB6Xuw5GBS22vx6hVuqAQ
JBAP/gPG78/VwYzs96g8vbH0ZK87IyriTXaJy5bCKAySWqKEuH70qqKWZku1FGcEKEg
u7zvBqMLE6sMwB0538+nrECQQD6JhRf2qBOSS00DktyD6T0ZTo81llMQgKENG0GFRxb
i68NwfKHkHLK2Jh08940StoY2AbgPMVFo5IbQCZstvb9AkEA1v94aB3FWUwzAdzEv1b
ICY1TBQlWj/g7m2yl8rvZx/8YypZWt5sRHEtJpboU+59SUSuVg40Ccbx5Qkn4dk+MkQ
JBANC5TW7d4bzw2kMBWnqGC0HR5VI2GLTEr4zYoc/ddZf9ace0+UWchewz5qJPii1f9
7ObLcGVc5Wz8onCcU1tLZECQDNo4l4N7lX+eg8W1u+nfJdtUE7Mnkc3HzdEMMTIwp92
JlzNMDbBinNbxki9OBi6gmrQzhLTp9qhPeAWYqEd7zY=
-----END PRIVATE KEY-----';
    
/**
1)dinpay_public_key，智付公钥，每个商家对应一个固定的智付公钥（不是使用工具生成的密钥merchant_public_key，不要混淆），
即为智付商家后台"公钥管理"->"智付公钥"里的绿色字符串内容,复制出来之后调成4行（换行位置任意，前面三行对齐），
并加上注释"-----BEGIN PUBLIC KEY-----"和"-----END PUBLIC KEY-----"
2)demo提供的dinpay_public_key是测试商户号1118004517的智付公钥，请自行复制对应商户号的智付公钥进行调整和替换。
3）使用智付公钥验证时需要调用openssl_verify函数进行验证,需要在php_ini文件里打开php_openssl插件
*/
const DINPAY_PUBLIC_KEY = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBteAp6tB90/OG6C6M5RhgKHbGajBZSumfk1st
d7oihCo+ZOL70GNdyysjduo5jyMy11Sc2cVrl2xmw4oN6IPq2GvP3hSTFpOmo8wnIBcsBecQFcWI
9bUOsJMXSyBYsHJrLHGzG+UAcFn/ZAVTWaI/7RRrhpfnWPo65dXpdFmsEQIDAQAB';
