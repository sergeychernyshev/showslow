#!/usr/bin/env perl

###########################################################################
##
## Copyright (c) 2010, Aaron Kulick, CBS Interactive 
## All rights reserved.
##
## Redistribution and use in source and binary forms, with or without 
## modification, are permitted provided that the following conditions 
## are met:
##
##     * Redistributions of source code must retain the above copyright 
##         notice, this list of conditions and the following disclaimer.
##     * Redistributions in binary form must reproduce the above 
##          copyright notice, this list of conditions and the following 
##          disclaimer in the documentation and/or other materials 
##          provided with the distribution.
##     * Neither the name of the CBS Interactive nor the names of its 
##          contributors may be used to endorse or promote products 
##          derived from this software without specific prior written 
##          permission.
##
## THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 
## "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT 
## LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR 
## A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT 
## HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
## SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT 
## LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
## DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY 
## THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT 
## (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE 
## OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
##
## CONTACT -=> Aaron Kulick <aaron.kulick@cbs.com>
###########################################################################

use warnings;
use strict;

# Avoid zombies...
$SIG{CHLD} = 'IGNORE';

use LWP::UserAgent;
use Getopt::Long;
# Perl Object Environment - http://poe.perl.org/
use POE qw(Wheel::Run);

# POE::Component::TSTP - handle control-z (if installed)
eval { require POE::Component::TSTP }
  and do { POE::Component::TSTP->create() if !$@; };

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
          . "usage:  $command --source URL --profile PATH --wait SEC [--verbose|debug]\n"
          . "    --firefox  path to Firefox binary ( default = /usr/bin/firefox )\n"
          . "    --source	uniform resource locator ( e.g. http://www.example.com/list )\n"
          . "    --profile  path to Firefox profile ( e.g. /home/foo/profile )\n"
          . "    --wait     thread execution time in seconds ( default = 30 )\n"
          . "    --display  x11 display ( e.g. :99 )\n"
          . "    --debug\n" . "\n"
    );

    die("\n");
}

my $debug;
my $firefox = "/usr/bin/firefox";
my @mozprofile;
my @source;
my $thread_wait = 30;
my $x11_display;

Getopt::Long::GetOptions(
    'debug|verbose' => \$debug,
    'display=s'     => \$x11_display,
    'firefox=s'     => \$firefox,
    'profile=s'     => \@mozprofile,
    'source=s'      => \@source,
    'wait=i'        => \$thread_wait,
) or usage("Usage ERROR:  Invalid command line option(s).");

usage("Usage ERROR:  At least 1 profile must be specified.")
  unless @mozprofile;

usage("Usage ERROR:  At least 1 URL source must be specified.")
  unless @source;

usage("Usage ERROR:  Must provide a valid x11 display.")
  unless defined $x11_display;

usage("Usage ERROR:  Must provide a valid path to Mozilla Firefox.")
  unless ( -e $firefox );

open( STDERR, '>/dev/null' )
  or die "ABORT:  Cannot open $!"
  unless defined $debug;

# Test profile arguments (create global variable with # of elements)
my $number_ff_profiles = ff_profiles(@mozprofile);
# Build an array of urls to test  (create global variable of elements)
my @testurls           = source_urls(@source);
my @thread_pids;

# Trap ctrl-c (threads run independently).
print STDERR "Trapping SIG{INT}.\n";
$SIG{INT}  = \&end_script;
print STDERR "Starting concurrent Mozilla Firefox thread(s):";
print STDERR "    Max Threads => $number_ff_profiles\n";

# subroutine - set number of concurrent threads (# threads == # profiles)
sub MAX_CONCURRENT_TASKS () { $number_ff_profiles }

# Handle ctrl-c (NEEDS MORE WORK)!
sub end_script {
    print "\nCAUGHT SIG{INT}... cleaning up!\n";
    close STDERR;
    sleep ($thread_wait);
    exit(0);
}

# subroutine - queries each source URL for test URLs or die
sub source_urls {
    my @lists = @_;
    my @array;
    print STDERR "Fetching URL source list(s):\n";
    foreach my $list (@lists) {
        print STDERR "    LWP::get $list => ";
        my $browser = LWP::UserAgent->new();
        my $res     = $browser->get($list)
          or die "LWP ERROR:  Error retrieving URL $list: $!";
        if ( !$res->is_success ) {
            print STDERR "FAIL.\n";
            my $error = $res->status_line;
            die "Source ERROR:  URL $list: $error\n";
        }
        else {
            print STDERR "SUCCESS.\n";
            @array = split( '\n', $res->content );
        }
    }
    print STDERR "DONE.\n";
    return @array;
}

# subroutine - verify profile dir exists and a prefs.js - (NOT BULLETPROOF!)
sub ff_profiles {
    my @paths = @_;
    print STDERR "Testing Mozilla Firefox profile(s):\n";
    foreach my $path (@paths) {
        print STDERR "    Profile $path => ";
        my $pref_file = $path . "/prefs.js";
        if ( !-d $path || !-e $pref_file ) {
            print STDERR "INVALID\n";
            die "Profile ERROR: Mozilla Firefox profile $path does not exist or is empty.";
        }
        print STDERR "VALID\n";
    }
    my $num_profiles = @paths;
    print STDERR "DONE.\n";
    return $num_profiles;
}

# subroutine - this actually spawns FF & kills it (FULLY AUTONOMOUS!)
sub do_stuff {
    my ( $url, $thread ) = @_;
    sleep rand(10);
    setpgrp();
    my $child_pid = fork();
    if ( $child_pid == 0 ) {
        sleep($thread_wait);
        kill -9, getpgrp($child_pid);
    }
    elsif ($child_pid) {
        push(@thread_pids, $child_pid);
        system "DISPLAY=$x11_display $firefox -no-remote -profile $mozprofile[$thread] $url";
    }
    else { die "ERROR:  Could not create fork: $!\n"; }
}

###########################################################################
#
#  All code below this line was sourced from the POE Cookbook.
#      URL: http://poe.perl.org/?POE_Cookbook/Child_Processes_3
#
#  All rights and copyright rest with the original author(s).
#
#  The recipes are distributed under the same terms as POE itself. 
#  POE, in turn, is distributed under the same terms as Perl.
#
#  Please see http://dev.perl.org/licenses/ for the Perl license.
#
###########################################################################

# Start the session that will manage all the children.  The _start and
# next_task events are handled by the same function.
POE::Session->create(
    inline_states => {
        _start     => \&start_task,
        next_task  => \&start_task,
        task_done  => \&handle_task_done,
        task_debug => \&handle_task_debug,
        sig_child  => \&sig_child,
    }
);

# Start as many tasks as needed so that the number of tasks is no more
# than MAX_CONCURRENT_TASKS.  Every wheel event is accompanied by the
# wheel's ID.  This function saves each wheel by its ID so it can be
# referred to when its events are handled.
sub start_task {
    my ( $kernel, $heap ) = @_[ KERNEL, HEAP ];
    while ( keys( %{ $heap->{task} } ) < MAX_CONCURRENT_TASKS ) {
        my $next_task = shift @testurls;
        my $task_id   = @testurls % $number_ff_profiles;
        last unless defined $next_task;
        print STDERR "    THREAD ID" . $task_id
          . "=> Testing URL $next_task with profile $mozprofile[$task_id]\n";
        my $task = POE::Wheel::Run->new(
            Program     => sub { do_stuff( $next_task, $task_id ) },
            StderrEvent => "task_debug",
            CloseEvent  => "task_done",
        );
        $heap->{task}->{ $task->ID } = $task;
        $kernel->sig_child( $task->PID, "sig_child" );
    }
}

# Catch and display information from the child's STDERR.  This was
# useful for debugging since the child's warnings and errors were not
# being displayed otherwise.
sub handle_task_debug {
    my $result = $_[ARG0];
    print STDERR ">> $result\n";
}

# The task is done.  Delete the child wheel, and try to start a new
# task to take its place.
sub handle_task_done {
    my ( $kernel, $heap, $task_id ) = @_[ KERNEL, HEAP, ARG0 ];
    delete $heap->{task}->{$task_id};
    $kernel->yield("next_task");
}

# Detect the CHLD signal as each of our children exits.
sub sig_child {
    my ( $heap, $sig, $pid, $exit_val ) = @_[ HEAP, ARG0, ARG1, ARG2 ];
    my $details = delete $heap->{$pid};

    #warn "$$: Child $pid exited";
}

$poe_kernel->run();
close STDERR;
exit 0;
