#!/usr/bin/env perl

###########################################################################
##
##  Copyright (c) 2010, Aaron Kulick, CBS Interactive 
##  All rights reserved.
##
##  THANK YOU:
##  The author would specifically like to thank the people on the IRC
##  server irc.perl.org in channel #poe for there extreme patience and
##  incalculable assistance without which this script would not work.
##
##  LICENSE:
##  Redistribution and use in source and binary forms, with or without 
##  modification, are permitted provided that the following conditions 
##  are met:
##
##     * Redistributions of source code must retain the above copyright 
##          notice, this list of conditions and the following disclaimer.
##     * Redistributions in binary form must reproduce the above 
##          copyright notice, this list of conditions and the following 
##          disclaimer in the documentation and/or other materials 
##          provided with the distribution.
##     * Neither the name of the CBS Interactive nor the names of its 
##          contributors may be used to endorse or promote products 
##          derived from this software without specific prior written 
##          permission.
##
##  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 
##  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT 
##  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR 
##  A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT 
##  HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
##  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT 
##  LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
##  DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY 
##  THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
##  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE 
##  OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
##
##  CONTACT -=> Aaron Kulick <aaron.kulick@cbs.com>
##
###########################################################################


###########################################################################
##
##  POE code blocks sourced from the POE Cookbook where indicated:
##      URL: http://poe.perl.org/?POE_Cookbook/Child_Processes_3
##
##  All rights and copyright rest with the original author(s).
##
##  The recipes are distributed under the same terms as POE itself. 
##  POE, in turn, is distributed under the same terms as Perl.
##
##  Please see http://dev.perl.org/licenses/ for the full body of the 
##  Perl license.
##
###########################################################################


use warnings;
use strict;


###########################################################################
##
## Global Variable Initialization
##
###########################################################################
my $SCRIPT_VERSION = "1.0.0";
my $SCRIPT_INFO = "Copyright 2010 - Aaron Kulick <aaron.kulick\@cbs.com>";
my $SCRIPT_URL = "http://code.google.com/p/showslow/source/browse/trunk/automation/test_harness.pl";
my $debug;
my $firefox = "/usr/bin/firefox";
my $help;
my @mozprofile;
my $number_ff_profiles;
my $quiet;
my @sessions;
my @source;
my @testurls;
my @threads;
my $timeout = 60;
my $version;
my $x11_display;


# Avoid zombies...  argghh... want Brains!
$SIG{CHLD} = 'IGNORE';


use LWP::UserAgent;
use Getopt::Long;
use Time::HiRes qw(time);


# Perl Object Environment - http://poe.perl.org/
use POE qw(Wheel::Run Filter::Reference);


# POE::Component::TSTP - handle control-z (if installed)
eval { require POE::Component::TSTP }
  and do { POE::Component::TSTP->create() if !$@; };


# subroutine - provides usage/help
sub usage {
    my $message = $_[0];
    if ( defined $message && length $message ) {
        $message .= "\n"
          unless $message =~ /\n$/;
    }

    my $command = $0;
    $command =~ s#^.*/##;

    print STDERR (
        $message,
        "\n"
          . "usage:  $command --display <DISPLAY> --firefox <PATH> --source <URL> \\\n"
          . "            --profile <PATH> [--timeout <SECONDS>] [--quiet] [--verbose]\n\n"
          . "    --display  x11 display ( e.g. ':99' )\n"
          . "    --firefox  path to Firefox binary ( default = /usr/bin/firefox )\n"
          . "    --profile  path to Firefox profile ( e.g. /home/foo/profile )\n"
          . "    --source   uniform resource locator ( e.g. http://www.example.com/list )\n"
	  . "    --quiet    supress debug messages ( default TRUE )\n"
          . "    --timeout  thread execution timeout in seconds ( default = 60 )\n"
          . "    --verbose  enable verbose ouput to STDOUT ( default FALSE )\n" 
          . "    --version  report the current version of $command\n"
          . "\n"
    );

    die("\n");
}


sub version {
    my $command = $0;
    my $PERL_VERSION = $];
    my $LWP_VERSION = $LWP::UserAgent::VERSION;
    my $TIME_VERSION = $Time::HiRes::VERSION;
    my $POE_VERSION = $POE::VERSION;

    $command =~ s#^.*/##;

    print STDOUT (
        "\n"
          . "    Script :           $command\n"
          . "    Author :           $SCRIPT_INFO\n"
          . "    Version :          $SCRIPT_VERSION\n"
          . "    URL :              $SCRIPT_URL\n\n"
	  . "    Perl :             v$PERL_VERSION\n"
	  . "    LWP::UserAgent :   v$LWP_VERSION\n"
	  . "    Time::HiRes :      v$TIME_VERSION\n"
	  . "    POE :              v$POE_VERSION\n"
          . "\n"
    );

    die("\n");
}


# subroutine - set number of concurrent threads (# threads == # profiles)
sub MAX_CONCURRENT_TASKS () { $number_ff_profiles }


# subroute - delete any running FF threads close up and quit.
sub end_script {
    
    print STDOUT "\nCAUGHT SIG{INT}... cleaning up!\n";

    my @pids = map { $_->PID } values %{ $_[HEAP]->{task} };

    foreach my $pid (@pids) {
        print STDERR ">> Terminating PID => $pid\n";
        kill -9, getpgrp($pid);
    }
    sleep (2);
    close VERBOSE;
    close QUIET;
    print STDOUT "Done.\n";
    exit(1);
}


# subroutine - queries each source URL for test URLs or die
sub source_urls {
    my @lists = @_;
    my @array;
    print VERBOSE "Fetching URL source list(s):\n";
    foreach my $list (@lists) {
        print VERBOSE "    LWP::get $list => ";
        my $browser = LWP::UserAgent->new();
        my $res     = $browser->get($list)
          or usage("LWP ERROR:  Error retrieving URL $list: $!");
        if ( !$res->is_success ) {
            print VERBOSE "FAIL.\n";
            my $error = $res->status_line;
            usage("Source ERROR:  URL $list: $error\n");
	    die("\n");
        }
        else {
            print VERBOSE "SUCCESS.\n";
            push @array, split( '\n', $res->content );
        }
    }
    print VERBOSE "DONE.\n\n";
    return @array;
}


# subroutine - verify profile dir exists and a prefs.js - (NOT BULLETPROOF!)
sub ff_profiles {
    my @paths = @_;
    my $count = 0;
    print VERBOSE "Testing Mozilla Firefox profile(s):\n";
    foreach my $path (@paths) {
        print VERBOSE "    Profile $path => ";
        my $pref_file = $path . "/prefs.js";
        if ( !-d $path || !-e $pref_file ) {
            print VERBOSE "INVALID\n";
            usage("Profile ERROR: Mozilla Firefox profile $path does not exist or is empty.");
            die("\n");
        }
        push @threads, $count++;
        print VERBOSE "VALID\n";
    }
    my $num_profiles = @paths;
    print VERBOSE "DONE.\n\n";
    return $num_profiles;
}


###########################################################################
##
##  All code below this line was sourced from the POE Cookbook.
##      URL: http://poe.perl.org/?POE_Cookbook/Child_Processes_3
##
##  All rights and copyright rest with the original author(s).
##
##  The recipes are distributed under the same terms as POE itself. 
##  POE, in turn, is distributed under the same terms as Perl.
##
##  Please see http://dev.perl.org/licenses/ for the full body of the
##  Perl license.
##
###########################################################################


###########################################################################
##
## BEGIN POE CODEBLOCK

sub handle_start{
    $_[KERNEL]->sig( INT => "sig_int" );
    $_[KERNEL]->yield("next_task");
}

# Start as many tasks as needed so that the number of tasks is no more
# than MAX_CONCURRENT_TASKS.  Every wheel event is accompanied by the
# wheel's ID.  This function saves each wheel by its ID so it can be
# referred to when its events are handled.
sub start_task {
    my ( $kernel, $heap ) = @_[ KERNEL, HEAP ];
    while ( keys( %{ $heap->{task} } ) < MAX_CONCURRENT_TASKS ) {
        my $url = shift @testurls;
	my $thread = shift @threads;
        my $profile = shift @mozprofile;
        last unless defined $url;
        my $clock = time();
        my $task = POE::Wheel::Run->new(
            Program		=> [ "DISPLAY=$x11_display $firefox -no-remote -profile $profile '$url'" ],
            StdoutEvent         => "task_result",
            StderrEvent         => "task_debug",
            CloseEvent          => "task_done",
        ) or die "CRITICAL FAULT>> cannot spawn POE::Wheel::Run object: $!\n";
	$heap->{task}->{$task->ID} = $task;
        $kernel->sig_child( $task->PID, "sig_child" );
	
        $heap->{wheel_alarm}->{$task->ID} = $kernel->delay_set( task_timeout => $timeout, $task->ID )
          or die "CRITICAL FAULT>> cannot set alarm: $!\n"; 
        $heap->{wheel_pid}->{$task->ID} = $task->PID;
        $heap->{wheel_thread}->{$task->ID} = $thread;
        $heap->{wheel_url}->{$task->ID} = $url;
        $heap->{wheel_profile}->{$task->ID} = $profile;
        print VERBOSE "    THREAD ID" . $thread
          . "=> $clock :: Testing URL $url with profile $profile\n";
    }
}


# Handle information returned from the task.  Since we're using
# POE::Filter::Reference, the $result is as it was created in the
# child process.  In this sample, it's a hash reference.
sub handle_task_result {
    my ( $heap, $result, $task_id ) = @_[ HEAP, ARG0, ARG1 ];
    my $thread = $heap->{wheel_thread}->{$task_id};
    print VERBOSE "    THREAD ID" . $thread . "=> $result\n";
}


# Catch and display information from the child's STDERR.  This was
# useful for debugging since the child's warnings and errors were not
# being displayed otherwise.
sub handle_task_debug {
    my ( $heap, $result, $task_id ) = @_[ HEAP, ARG0, ARG1 ];
    my $thread = $heap->{wheel_thread}->{$task_id};
    print QUIET "    THREAD ID" . $thread . "=> DEBUG (FIREFOX)>> $result\n";
}


# The task is done.  Delete the child wheel, and try to start a new
# task to take its place.
sub handle_task_done {
    my ( $kernel, $heap, $task_id ) = @_[ KERNEL, HEAP, ARG0 ];
    $kernel->alarm_remove( delete $heap->{wheel_alarm}->{$task_id} )
      or print STDERR "WARNING>> cannot delete alarm $heap->{wheel_alarm}->{$task_id}: $!\n";
    my $thread = $heap->{wheel_thread}->{$task_id};
    my $url = $heap->{wheel_url}->{$task_id};
    my $profile = $heap->{wheel_profile}->{$task_id};
    my $pid = $heap->{wheel_pid}->{$task_id};
    delete $heap->{task}->{$task_id};
    my $clock = time();
    push @mozprofile, $profile;
    push @threads, $thread;
    print VERBOSE "    THREAD ID" . $thread . "=> $clock :: DONE :: $url\n";
    $kernel->yield("next_task");
}


# Handle firefox not terminating normal before timeout
sub handle_task_timeout { 
    my $task_id = $_[ARG0];
    my $thread = $_[HEAP]->{wheel_thread}->{$task_id};
    my $url = $_[HEAP]->{wheel_url}->{$task_id};
    my $profile = $_[HEAP]->{wheel_profile}->{$task_id};
    my $pid = $_[HEAP]->{wheel_pid}->{$task_id};
    return unless exists $_[HEAP]->{task}->{$task_id};
    $_[HEAP]->{task}->{$task_id}->kill(-9);
    delete $_[HEAP]->{task}->{$task_id};
    my $clock = time();
    push @mozprofile, $profile;
    push @threads, $thread;
    print VERBOSE "    THREAD ID" . $thread . "=> $clock :: TIMEOUT $profile :: $url\n";
    $_[KERNEL]->yield("next_task");
}


# Handle session termination explicitly.
sub handle_task_shutdown {
    my ($kernel, $session, $heap) = @_[KERNEL, SESSION, HEAP];
    # delete all wheels.
    delete $heap->{wheel};
    # clear your alias
    $kernel->alias_remove($heap->{alias});
    # clear all alarms you might have set
    $kernel->alarm_remove_all();
    # get rid of external ref count
    $kernel->refcount_decrement($session, 'my ref name');
    # propagate the message to children
    $kernel->post($heap->{child_session}, 'shutdown');
    return;
}


# Detect the CHLD signal as each of our children exits.
sub sig_child {
    my ( $heap, $sig, $pid, $exit_val ) = @_[ HEAP, ARG0, ARG1, ARG2 ];
    print VERBOSE "SIG_CHILD :: pid = $pid\n";
    my $details = delete $heap->{$pid};

    warn "$$: Child $pid exited";

}

##
## END POE CODE BLOCK
##
###########################################################################


###########################################################################
##
## BEGIN MAIN PROGRAM EXECUTION
##
###########################################################################

# argument processing and validation
Getopt::Long::GetOptions(
    'firefox=s'         => \$firefox,
    'display=s'         => \$x11_display,
    'help'              => \$help,
    'profile=s'         => \@mozprofile,
    'quiet'             => \$quiet,
    'source=s'          => \@source,
    'timeout=i'         => \$timeout,
    'verbose'           => \$debug,
    'version'		=> \$version,
) or usage("Usage ERROR:  Invalid command line option(s).");

usage("Usage HELP:")
  unless ! defined $help || exists $ARGV[1];

version() unless ! defined $version;

usage("Usage ERROR:  At least 1 source, 1 profile and a display must be specified.")
  unless @mozprofile && @source & defined $x11_display;

usage("Usage ERROR:  Must provide a valid path to Mozilla Firefox.")
  unless ( -e $firefox );


# verbose mode
if ( defined $debug ) {
    open( VERBOSE, '>&STDOUT' )
} else {
    open( VERBOSE, '>/dev/null' )
    or die "ABORT:  Cannot open $!";
}


# quiet mode
if ( ! defined $quiet ) {
    open( QUIET, '>&STDERR' )
} else {
    open( QUIET, '>/dev/null' )
    or die "ABORT:  Cannot open $!";
}


# Test profile arguments (create global variable with # of elements)
$number_ff_profiles = ff_profiles(@mozprofile);


# Build an array of urls to test  (create global variable of elements)
@testurls = source_urls(@source);


# Start the test cycle.
print VERBOSE "Starting concurrent Mozilla Firefox thread(s):\n";
print VERBOSE "    Max Threads => $number_ff_profiles\n";


# Start the session that will manage all the children.  The _start and
# next_task events are handled by the same function.
POE::Session->create(
    inline_states => {
        _start          => \&handle_start,
        next_task       => \&start_task,
        _stop           => \&handle_task_shutdown,
        task_result     => \&handle_task_result,
        task_done       => \&handle_task_done,
        task_debug      => \&handle_task_debug,
        task_timeout    => \&handle_task_timeout,
        sig_child       => \&sig_child,
	sig_int         => \&end_script,
    }
) or die "CRITICAL FAULT>> cannot spawn POE::Session object: $!\n";


# Launch the session.
$poe_kernel->run();


# Finish.
print VERBOSE "DONE.\n";
close VERBOSE;
close QUIET;
exit 0;
