<?php
class Config{
    private $cfg = array(
        'url'=>'https://pay.swiftpass.cn/pay/gateway',     /*支付接口请求地址，请联系技术支持确认 */
        'mchId'=>'102516468031',   /* 商户号，建议用正式的，于申请成功后的开户邮件中获取，若未开户需用测试的请联系技术支持 */
        'version'=>'2.0',
        'sign_type'=>'RSA_1_256',
        'public_rsa_key'=>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqNxzebovJ6R+LF0jFyJD4vgdvj+Apmb5h+pW3T0EtDzWZAr7tyiSAtNedYvRjJCqN5cYw0rIwGMZFbD3lQHbJGC+IvpqXwPB8AWqRAwItI82fo2+AyHkq11yE27IgOjSrKofgg3GWJ6SSQonYuXZ0c09chXXiZPKYe0zRbvq83kAVsYDu1sMwi8mfiVff6CIALsehs1MOjmdLW40N1CicVmJaWuh2yee+sj1/0xMOlV1LyJq63hShBD7T93qpGbHoNkpdz+BFc2byrhv1idbB4DRbUiKynzj3FX2Nz8Dv9TFQv8p2Z8dIOst890atv3P8DO7a9FI8I1reLvFDdyPawIDAQAB',   /* RSA验签平台公钥，建议用正式的，登录商户后台查看，若未开户需用测试的请联系技术支持 */
        'private_rsa_key'=>'MIIEpgIBAAKCAQEA0G7Ac/lIzq9wpwaGt2bOr07UIOEJdeOuLM1S+2YCfJG9Qphl11/7/9V1gXWhTO13StK6bUBGRkiz+GF3bTy4lLb/jchd7tb/cmmv2cXodqtof1K6t3rmSVomzHw/OfQNZrSaMwtodkg+QWrbK9CnVXDJRthbj9srOVQZFXQnk/jD/BUhLbXqSy4PjAt0k+y/DA3w5fr5ctT4BVqw3IQHYNQLyb330q4bD/UPWTVj9m17zm9OofhbkRA0l8Y+3lcXUDKjRw696hjY7OSSZVxLp308BQT/VSYCZx4jLvN1yZ+X9gwG7dz+gFh3+uAv1Zh0C2J8ITba/DUGc3ame/hCIQIDAQABAoIBAQCmGTwgr21H2CNL1zWP/cuDhKwjL3Ickj4A0fbpBFfC8VkDMvMleQYW0AJ+EkFiTnKcG+YYnfnilJlmvDUxxgvJ5zMrx5qjdI3InVRXlRE1UE9L9594C+ZsWf1FQ1YXVtc/G3kuaE7sw5FpDEBwYCyZN/IOFOiScTO20b/TiubnUQv5oqqQG0mcibV0bXRph4iFyKHxFLafvw4SXpUHfl03CTisG83HY06WleWb6vnMMz/mp+cBW8fwmoIP4fVFN0QqzFHpafEgkPRP5peI0CAniNnbls8CzjUbIiyNZI0pnNcDSYaZCJA7X0VXirfK87OW0RVf1rcYDCs67zt00+MpAoGBAPVL6iHAr5Qv0mhTxo+Y9NdcVgf2cdDQV0TIG2RAMej2SgShTLCXSjyv0jbKNEHnRs9qke4ZBZqdmmqWqzOrKat5jT18HXUXx06McCOVMGPBmJ8Gz0B1A9tgudZJuV/ErFdJ/X0H+EpM/aD0EYPW592njiVkLHMGVofDgOnfehlbAoGBANmHDHp0409GT4k65t1Rps9RcRwroNh1hhLCDX2KLWkuUtrjcVtza/70B7BsfjpJrFCZltKX0P5l03pyViTICqkwURVqLqbp2z8j4lLWNk/+ZZ8AJdTd5Gj/CuxImZvqVWSScoGIaS7N13CESFjQO7d78+K9BUY2f2nwCJtO8K8zAoGBAIyImLQLu8wPdeGVlZ3xiNzVpuha9iwnIMhkSOUvriiE6jUq4FAP7VVFeg8v266iPTxaFw8tQLurbauBdMZeWrpGInhGYm4SWHqVFS4drCKK6NC7SwPnxnTqPq4ZgN3wRLihyFvYtBSFdY3AJ0S8XAzukQ61DI495FdV18al5UMfAoGBALhxMxpuHAMu2efBItnMDwXAx4icUaDYXZtwIOIulHyXw7dHnOlu/8ZJAnAMPieMKmiZInJkOdhLXLp5UiOT3r5AcrAWvYHXzohGE/QrIBhJ276q8GkC0FZa0tcwY9b5JfjF2AOPN6hw7ti/wVxVDB1zI4NAxMUZFoYr+hA+KgRTAoGBAO3zRCnsZKviqSnqsxvyNchI2lQQRN0gj0I5INJL7U8qCt4Ql9RTjqaJuErxjW+7ar7ihfbYQEoJt1CgOETYpjcrf76c2OF9eIWDKFJvCfquhINOKUHn4dYsp6eX5Xc1mt9eQoZ9Jvoh//OFZ3jdiqAzsz/16epnWn7hXVfS2Tmm'   /* RSA签名私钥，建议用正式的，商户开发人员本地生成，若未开户需用测试的请联系技术支持 */
       );
    
    public function C($cfgName){
        return $this->cfg[$cfgName];
    }
}
?>