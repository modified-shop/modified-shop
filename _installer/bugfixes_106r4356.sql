#h-h-h - 2012-07-07 - Get ready for IPv6
ALTER TABLE campaigns_ip MODIFY user_ip VARCHAR (39);
ALTER TABLE coupon_gv_queue MODIFY ipaddr VARCHAR (39);
ALTER TABLE customers_ip MODIFY customers_ip VARCHAR (39);
ALTER TABLE orders MODIFY customers_ip VARCHAR (39);
ALTER TABLE whos_online MODIFY ip_address VARCHAR (39);
ALTER TABLE coupon_redeem_track MODIFY redeem_ip VARCHAR (39);