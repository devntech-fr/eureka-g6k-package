<?php

/*
The MIT License (MIT)

Copyright (c) 2015-2020 Jacques Archimède

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

namespace Devntech\G6K\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

use App\Security\UserManagerInterface;

/**
 * Locks an user into the users database.
 *
 */
class LockUserSecurityCommand extends CommandBase {

	private $userManager;

	/**
	 * @inheritdoc
	 */
	public function __construct(string $projectDir, UserManagerInterface $userManager) {
		parent::__construct($projectDir, "Locks a user into the users database.");
		$this->userManager = $userManager;
	}

	/**
	 * @inheritdoc
	 */
	protected function getCommandName() {
		return 'g6k:security:user:lock';
	}

	/**
	 * @inheritdoc
	 */
	protected function getCommandDescription() {
		return $this->translator->trans('Locks a user into the users database.');
	}

	/**
	 * @inheritdoc
	 */
	protected function getCommandHelp() {
		return 
			  $this->translator->trans("This command allows you lock a user into the users database.")."\n"
			. "\n"
			. $this->translator->trans("You must provide:")."\n"
			. $this->translator->trans("- the username of the user.")."\n"
		;
	}

	/**
	 * @inheritdoc
	 */
	protected function getCommandArguments() {
		return [
			[
				'username',
				InputArgument::REQUIRED,
				$this->translator->trans('The username of the user.')
			]
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function getCommandOptions() {
		return array();
	}

	/**
	 * Checks the argument of the current command (g6k:security:user:lock).
	 *
	 * @param   \Symfony\Component\Console\Input\InputInterface $input The input interface
	 * @param   \Symfony\Component\Console\Output\OutputInterface $output The output interface
	 * @return  void
	 *
	 */
	protected function interact(InputInterface $input, OutputInterface $output) {
		$questions = array();
		if (!$input->getArgument('username')) {
			$question = new Question('Please enter an username : ');
			$question->setValidator(function ($username) {
				if (empty($username)) {
					throw new \Exception('The username can not be empty');
				}

				return $username;
			});
			$questions['username'] = $question;
		}
		foreach ($questions as $name => $question) {
			$answer = $this->getHelper('question')->ask($input, $output, $question);
			$input->setArgument($name, $answer);
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		parent::execute($input, $output);
		$username = $input->getArgument('username');

		try {
			$user = $this->userManager->findUserByUsername($username);
			if ($user === null) {
				$this->failure($output, "The user '%s%' doesn't exists.", ['%s%' => $username]);
				return 1;
			} else {
				$user->setLocked(true);
				$this->userManager->updateUser($user);
				$this->success($output, "Locked user '%s%'", ['%s%' => $username]);
				return 0;
			}
		} catch(\Exception $e) {
			$this->failure($output, "The user '%s%' can't be locked. Reason : %r%", ['%s%' => $username, '%r%' => $e->getMessage()]);
			return 1;
		}
	}

}
