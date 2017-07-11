#!/bin/bash
# generated installation key and server URI from install
export PATH=/usr/local/bin:$PATH
auth_key="__SERVER_AUTHKEY_SET_ME__"
need_patched=false
server_uri="__SERVER_URI_SET_ME__"
submit_patch_uri="${server_uri}client/send_patches.php"
# set client_path
if [[ -d /opt/patch_client ]]; then
        client_path="/opt/patch_client/"
else
        client_path="/opt/patch_manager/"
fi
# if $client_path does not exist
if [[ ! -f "${client_path}.patchrc" ]]; then
	echo "Please run ${client_path}check-in.sh as root (sudo) before trying to run this manually"
	exit 0
fi
# load the file
. ${client_path}.patchrc
rm -rf /tmp/patch_$client_key > /dev/null 2>&1
if [[ -f /etc/lsb-release && -f /etc/debian_version ]]; then
        os=$(lsb_release -s -d|head -1|awk {'print $1'})
elif [[ -f /etc/debian_version ]]; then
        os="$(cat /etc/issue|head -n 1|awk {'print $1'})"
elif [[ -f /etc/redhat-release ]]; then
        os=$(cat /etc/redhat-release|head -1|awk {'print $1'})
        if [[ "$os" = "Red" && $(grep -i enterprise /etc/redhat-release) != "" ]]; then
                os="RHEL"
        elif [[ "$os" = "Red" ]]; then
                os="RHEL"
        fi
else
	os=$(uname -s -r|head -1|awk {'print $1'})
fi
# remove any special characters
os=$(echo $os|sed -e 's/[^a-zA-Z0-9]//g')
# begin update checks
if [[ "$os" = "CentOS" ]] || [[ "$os" = "Fedora" ]] || [[ "$os" = "RHEL" ]]; then
	need_patched=true
        yum -q check-update| while read i
        do
                i=$(echo $i) #this strips off yum's irritating use of whitespace
                if [[ "${i}x" != "x" ]]
                then
                        UVERSION=${i#*\ }
                        UVERSION=${UVERSION%\ *}
                        PNAME=${i%%\ *}
                        PNAME=${PNAME%.*}
                        patches_to_install=$(echo $(rpm -q "${PNAME}" --qf '%{NAME}:::%{VERSION}:::')${UVERSION})
                        echo "$patches_to_install" >> /tmp/patch_$client_key
                fi
        done
elif test "$os" = Ubuntu -o "$os" = Debian -o "$os" = Devuan -o "$os" = Raspbian; then
        need_patched="true"
        #apt-get --just-print upgrade 2>&1 | perl -ne 'if (/Inst\s([\w,\-,\d,\.,~,:,\+]+)\s\[([\w,\-,\d,\.,~,:,\+]+)\]\s\(([\w,\-,\d,\.,~,:,\+]+)\)? /i) {print "$1:::$2:::$3\n"}'
        patches_to_install=$(apt-get --just-print upgrade 2>&1 | perl -ne 'if (/Inst\s([\w,\-,\d,\.,~,:,\+]+)\s\[([\w,\-,\d,\.,~,:,\+]+)\]\s\(([\w,\-,\d,\.,~,:,\+]+)\)? /i) {print "$1:::$2:::$3\n"}')
	echo "$patches_to_install" >> /tmp/patch_$client_key
elif [[ "$os" = "Linux" ]]; then
        echo "unspecified $os not supported"
        exit 0
fi
if test "$node_dirs" -a \( -x /usr/local/bin/snyk -o -x /usr/bin/snyk \); then
	for dir in $node_dirs
	do
		test -d $dir -a -s $dir/package.json || continue
		cd $dir
		snyk --dry-run test 2>/dev/null|awk '/Vulnerability found on/{print $NF}'|sort -u|while read line
			do
				pname=`echo $line|cut -d@ -f1`
				dvers=$(snyk test $line 2>/dev/null|sed 's|(triggers upgrades to .*)||'|awk "/Should be upgraded to .*$pname@/{print \$NF}"|tail -1|cut -d@ -f2)
				iurl=$(snyk test $line 2>/dev/null|awk "/Info: .*$pname/{print \$2;exit;}")
				echo "$line:::$dvers:::$iurl"|sed 's|@|:::|'
			done >>/tmp/patch_$client_key
		cd - >/dev/null
	done
	test -s /tmp/patch_$client_key && need_patched=true
fi
if $need_patched; then
        patch_list=$(cat /tmp/patch_$client_key)
        curl -k -s -H "X-CLIENT-KEY: $client_key" $submit_patch_uri -d "$patch_list"
        rm -rf /tmp/patch_$client_key > /dev/null 2>&1
fi
