FROM invanos/b_setup:v1.1

#### ADD USER ###
ENV USER=demo 

RUN useradd -ms /bin/bash ${USER}
RUN mkdir -p /home/${USER}/public_html
RUN mkdir -p /home/${USER}/ssl
RUN mkdir -p /home/${USER}/logs

#COPY . /home/${USER}/public_html
ADD code.tar.gz /home/${USER}/public_html

### Permission
RUN chmod 755 /home/${USER}
RUN chmod 755 /home/${USER}/public_html
RUN chown -R $USER:$USER /home/${USER}/public_html

### php session
RUN chmod 777 /var/lib/php/session 

#### PHP socket issue creation
RUN mkdir /run/php-fpm

##### START PHP and NGINX
CMD ["/bin/bash","-c","/usr/sbin/php-fpm --daemonize && export USER N_WC N_W_P N_WRNF N_KAT N_CBBS N_CBFS N_CHBS N_LCHB N_SRIF N_ST N_MCH N_SF N_TCP_NP N_FBN N_FP N_SN N_RHD N_SCN N_SKN N_STCN N_PPV N_PCT N_PST N_PRT N_STT N_PB N_PBS N_FNML N_FNMET N_FRT N_FCT N_FB N_FBS && envsubst '$N_FBN $N_FP $N_SN $N_RHD $N_SCN $N_SKN $N_STCN $N_PPV $N_PCT $N_PST $N_PRT $N_STT $N_PB $N_PBS $N_FNML $N_FNMET $N_FRT $N_FCT $N_FB $N_FBS $USER' < /etc/nginx/conf.d/ws_magento.conf > /etc/nginx/conf.d/ws_magento.conf && envsubst '$N_WRNF $N_WC $N_W_P $N_KAT $N_CBBS $N_CBFS $N_CHBS $N_LCHB $N_SRIF $N_ST $N_MCH $N_SF $N_TCP_NP' < /etc/nginx/nginx.conf > /etc/nginx/nginx.conf && exec nginx -g 'daemon off;'"]" 

