<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * RFC 822 Email address list validation Utility
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2001-2021, Chuck Hagenbuch & Richard Heyes
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * 1. Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Mail
 * @package     Mail
 * @author      Richard Heyes <richard@phpguru.org>
 * @author      Chuck Hagenbuch <chuck@horde.org
 * @copyright   2001-2021 Richard Heyes
 * @license     http://opensource.org/licenses/BSD-3-Clause New BSD License
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/Mail/
 */

/**
 * RFC 822 Email address list validation Utility
 *
 * What is it?
 *
 * This class will take an address string, and parse it into it's consituent
 * parts, be that either addresses, groups, or combinations. Nested groups
 * are not supported. The structure it returns is pretty straight forward,
 * and is similar to that provided by the imap_rfc822_parse_adrlist(). Use
 * print_r() to view the structure.
 *
 * How do I use it?
 *
 * $address_string = 'My Group: "Richard" <richard@localhost> (A comment), ted@example.com (Ted Bloggs), Barney;';
 * $structure = Mail_RFC822::parseAddressList($address_string, 'example.com', true)
 * print_r($structure);
 *
 * @author  Richard Heyes <richard@phpguru.org>
 * @author  Chuck Hagenbuch <chuck@horde.org>
 * @version $Revision$
 * @license BSD
 * @package Mail
 */
class Mail_RFC822
{
    /**
     * The address being parsed by the RFC822 object.
     * @var string $address
     */
    public string $address = '';

    /**
     * The default domain to use for unqualified addresses.
     * @var string $default_domain
     */
    public string $default_domain = 'localhost';

    /**
     * Should we return a nested array showing groups, or flatten everything?
     * @var bool $nestGroups
     */
    public bool $nestGroups = true;

    /**
     * Whether or not to validate atoms for non-ascii characters.
     * @var bool $validate
     */
    public bool $validate = true;

    /**
     * The array of raw addresses built up as we parse.
     * @var array $addresses
     */
    public array $addresses = [];

    /**
     * The final array of parsed address information that we build up.
     * @var array $structure
     */
    public array $structure = [];

    /**
     * The current error message, if any.
     * @var string $error
     */
    public ?string $error = null;

    /**
     * An internal counter/pointer.
     * @var int $index
     */
    public ?int $index = null;

    /**
     * The number of groups that have been found in the address list.
     * @var int $num_groups
     * @access public
     */
    public int $num_groups = 0;

    /**
     * A variable so that we can tell whether or not we're inside a
     * Mail_RFC822 object.
     * @var bool $mailRFC822
     */
    public bool $mailRFC822 = true;

    /**
    * A limit after which processing stops
    * @var int $limit
    */
    public ?int $limit = null;

    /**
     * Sets up the object. The address must either be set here or when
     * calling parseAddressList(). One or the other.
     * @param string  $address         The address(es) to validate.
     * @param string  $default_domain  Default domain/host etc. If not supplied, will be set to localhost.
     * @param bool $nest_groups     Whether to return the structure with groups nested for easier viewing.
     * @param bool $validate Whether to validate atoms.
     *                       Turn this off if you need to run addresses through before encoding the personal names,
     *                       for instance.
     */
    public function __construct(
        string $address = null,
        string $default_domain = null,
        bool $nest_groups = null,
        bool $validate = null,
        int $limit = null
    ) {
        if (isset($address)) {
            $this->address = $address;
        }
        if (isset($default_domain)) {
            $this->default_domain = $default_domain;
        }
        if (isset($nest_groups)) {
            $this->nestGroups = $nest_groups;
        }
        if (isset($validate)) {
            $this->validate = $validate;
        }
        if (isset($limit)) {
            $this->limit = $limit;
        }
    }

    /**
     * Starts the whole process. The address must either be set here
     * or when creating the object. One or the other.
     * @param string  $address         The address(es) to validate.
     * @param string  $default_domain  Default domain/host etc.
     * @param bool $nest_groups     Whether to return the structure with groups nested for easier viewing.
     * @param bool $validate Whether to validate atoms.
     *                       Turn this off if you need to run addresses through before encoding the personal names,
     *                       for instance.
     * @return array A structured array of addresses.
     */
    public function parseAddressList(
        string $address = null,
        string $default_domain = null,
        bool $nest_groups = null,
        bool $validate = null,
        int $limit = null
    ): array {
        if (!isset($this, $this->mailRFC822)) {
            $obj = new Mail_RFC822($address, $default_domain, $nest_groups, $validate, $limit);
            return $obj->parseAddressList();
        }

        if (isset($address)) {
            $this->address = $address;
        }
        if (isset($default_domain)) {
            $this->default_domain = $default_domain;
        }
        if (isset($nest_groups)) {
            $this->nestGroups = $nest_groups;
        }
        if (isset($validate)) {
            $this->validate = $validate;
        }
        if (isset($limit)) {
            $this->limit = $limit;
        }

        $this->structure = [];
        $this->addresses = [];
        $this->error = null;
        $this->index = null;

        // Unfold any long lines in $this->address.
        $this->address = preg_replace('/\r?\n/', "\r\n", $this->address);
        $this->address = preg_replace('/\r\n(\t| )+/', ' ', $this->address);

        $tmp_address = false;
        while ($tmp_address = $this->_splitAddresses($this->address)) {
            $this->address = $tmp_address;
        }

        if ($tmp_address === false || isset($this->error)) {
            // mjansen patch 14 Ap 2016 start
            throw new ilMailException($this->error);
            // mjansen patch 14 Ap 2016 end
        }

        // Validate each address individually.  If we encounter an invalid
        // address, stop iterating and return an error immediately.
        foreach ($this->addresses as $address) {
            $valid = $this->_validateAddress($address);

            if ($valid === false || isset($this->error)) {
                // mjansen patch 14 Ap 2016 start
                throw new ilMailException($this->error);
                // mjansen patch 14 Ap 2016 end
            }

            if (!$this->nestGroups) {
                $this->structure = array_merge($this->structure, $valid);
            } else {
                $this->structure[] = $valid;
            }
        }

        return $this->structure;
    }

    /**
     * Splits an address into separate addresses.
     *
     * @param string $address The addresses to split.
     * @return bool|string
     */
    protected function _splitAddresses(string $address)
    {
        if (!empty($this->limit) && count($this->addresses) === $this->limit) {
            return false;
        }

        if (!isset($this->error) && $this->_isGroup($address)) {
            $split_char = ';';
            $is_group = true;
        } elseif (!isset($this->error)) {
            $split_char = ',';
            $is_group = false;
        } elseif (isset($this->error)) {
            return false;
        }

        // Split the string based on the above ten or so lines.
        $parts = explode($split_char, $address);
        $string = $this->_splitCheck($parts, $split_char);

        // If a group...
        if ($is_group) {
            // If $string does not contain a colon outside of
            // brackets/quotes etc then something's fubar.

            // First check there's a colon at all:
            if (strpos($string, ':') === false) {
                $this->error = 'Invalid address: ' . $string;
                return false;
            }

            // Now check it's outside of brackets/quotes:
            if (!$this->_splitCheck(explode(':', $string), ':')) {
                return false;
            }

            // We must have a group at this point, so increase the counter:
            $this->num_groups++;
        }

        // $string now contains the first full address/group.
        // Add to the addresses array.
        $this->addresses[] = [
            'address' => trim($string),
            'group' => $is_group,
        ];

        // Remove the now stored address from the initial line, the +1
        // is to account for the explode character.
        $address = trim((string) substr($address, strlen($string) + 1));

        // If the next char is a comma and this was a group, then
        // there are more addresses, otherwise, if there are any more
        // chars, then there is another address.
        if ($is_group && $address[0] === ',') {
            $address = trim(substr($address, 1));
            return $address;
        }

        return $address;
    }

    /**
     * Checks for a group at the start of the string.
     *
     * @param string $address The address to check.
     * @return bool Whether or not there is a group at the start of the string.
     */
    protected function _isGroup(string $address): bool
    {
        // First comma not in quotes, angles or escaped:
        $parts = explode(',', $address);
        $string = $this->_splitCheck($parts, ',');

        // Now we have the first address, we can reliably check for a
        // group by searching for a colon that's not escaped or in
        // quotes or angle brackets.
        if (count($parts = explode(':', $string)) > 1) {
            $string2 = $this->_splitCheck($parts, ':');
            return ($string2 !== $string);
        }

        return false;
    }

    /**
     * A common function that will check an exploded string.
     *
     * @param array $parts The exloded string.
     * @param string $char  The char that was exploded on.
     * @return string An empty string if the string contains unclosed quotes/brackets, or the string on success.
     */
    protected function _splitCheck(array $parts, string $char): string
    {
        $string = $parts[0];

        for ($i = 0, $iMax = count($parts); $i < $iMax; $i++) {
            if ($this->_hasUnclosedQuotes($string)
                || $this->_hasUnclosedBrackets($string, '<>')
                || $this->_hasUnclosedBrackets($string, '[]')
                || $this->_hasUnclosedBrackets($string, '()')
                || substr($string, -1) === '\\') {
                if (isset($parts[$i + 1])) {
                    $string .= $char . $parts[$i + 1];
                } else {
                    $this->error = 'Invalid address spec. Unclosed bracket or quotes';
                    return '';
                }
            } else {
                $this->index = $i;
                break;
            }
        }

        return $string;
    }

    /**
     * Checks if a string has unclosed quotes or not.
     *
     * @param string $string  The string to check.
     * @return bool True if there are unclosed quotes inside the string, false otherwise.
     */
    protected function _hasUnclosedQuotes(string $string): bool
    {
        $string = trim($string);
        $iMax = strlen($string);
        $in_quote = false;
        $i = $slashes = 0;

        for (; $i < $iMax; ++$i) {
            switch ($string[$i]) {
                case '\\':
                    ++$slashes;
                    break;

                case '"':
                    if ($slashes % 2 === 0) {
                        $in_quote = !$in_quote;
                    }
                    // Fall through to default action below.

                    // no break
                default:
                    $slashes = 0;
                    break;
            }
        }

        return $in_quote;
    }

    /**
     * Checks if a string has an unclosed brackets or not. IMPORTANT:
     * This function handles both angle brackets and square brackets;
     *
     * @param string $string The string to check.
     * @param string $chars  The characters to check for.
     * @return bool True if there are unclosed brackets inside the string, false otherwise.
     */
    protected function _hasUnclosedBrackets(string $string, string $chars): bool
    {
        $num_angle_start = substr_count($string, $chars[0]);
        $num_angle_end = substr_count($string, $chars[1]);

        $this->_hasUnclosedBracketsSub($string, $num_angle_start, $chars[0]);
        $this->_hasUnclosedBracketsSub($string, $num_angle_end, $chars[1]);

        if ($num_angle_start < $num_angle_end) {
            $this->error = 'Invalid address spec. Unmatched quote or bracket (' . $chars . ')';
            return false;
        }

        return ($num_angle_start > $num_angle_end);
    }

    /**
     * Sub function that is used only by hasUnclosedBrackets().
     *
     * @param string $string The string to check.
     * @param int &$num    The number of occurences.
     * @param string $char   The character to count.
     * @return int The number of occurences of $char in $string, adjusted for backslashes.
     */
    protected function _hasUnclosedBracketsSub(string $string, int &$num, string $char): int
    {
        $parts = explode($char, $string);
        for ($i = 0, $iMax = count($parts); $i < $iMax; $i++) {
            if (substr($parts[$i], -1) === '\\' || $this->_hasUnclosedQuotes($parts[$i])) {
                $num--;
            }
            if (isset($parts[$i + 1])) {
                $parts[$i + 1] = $parts[$i] . $char . $parts[$i + 1];
            }
        }

        return $num;
    }

    /**
     * Function to begin checking the address.
     *
     * @param array $address The address to validate.
     * @return mixed False on failure, or a structured array of address information on success.
     */
    protected function _validateAddress(array $address)
    {
        $is_group = false;
        $addresses = [];

        if ($address['group']) {
            $is_group = true;

            // Get the group part of the name
            $parts = explode(':', $address['address']);
            $groupname = $this->_splitCheck($parts, ':');
            $structure = [];

            // And validate the group part of the name.
            if (!$this->_validatePhrase($groupname)) {
                $this->error = 'Group name did not validate.';
                return false;
            }

            if ($this->nestGroups) {
                // Don't include groups if we are not nesting
                // them. This avoids returning invalid addresses.
                $structure = new stdClass();
                $structure->groupname = $groupname;
            }

            $address['address'] = ltrim(substr($address['address'], strlen($groupname . ':')));
        }

        // If a group then split on comma and put into an array.
        // Otherwise, Just put the whole address in an array.
        if ($is_group) {
            while (strlen($address['address']) > 0) {
                $parts = explode(',', $address['address']);
                $addresses[] = $this->_splitCheck($parts, ',');
                $address['address'] = trim(substr($address['address'], strlen(end($addresses) . ',')));
            }
        } else {
            $addresses[] = $address['address'];
        }

        // Trim the whitespace from all of the address strings.
        array_map('trim', $addresses);

        // Validate each mailbox.
        // Format could be one of: name <geezer@domain.com>
        //                         geezer@domain.com
        //                         geezer
        // ... or any other format valid by RFC 822.
        for ($i = 0, $iMax = count($addresses); $i < $iMax; $i++) {
            if (!$this->validateMailbox($addresses[$i])) {
                if (empty($this->error)) {
                    $this->error = 'Validation failed for: ' . $addresses[$i];
                }
                return false;
            }
        }

        // Nested format
        if ($this->nestGroups) {
            if ($is_group) {
                $structure->addresses = $addresses;
            } else {
                $structure = $addresses[0];
            }

            // Flat format
        } elseif ($is_group) {
            $structure = array_merge($structure, $addresses);
        } else {
            $structure = $addresses;
        }

        return $structure;
    }

    /**
     * Function to validate a phrase.
     *
     * @param string $phrase The phrase to check.
     * @return bool Success or failure.
     */
    protected function _validatePhrase(string $phrase): bool
    {
        // Splits on one or more Tab or space.
        $parts = preg_split('/[ \\x09]+/', $phrase, -1, PREG_SPLIT_NO_EMPTY);

        $phrase_parts = [];
        while (count($parts) > 0) {
            $phrase_parts[] = $this->_splitCheck($parts, ' ');
            for ($i = 0; $i < $this->index + 1; $i++) {
                array_shift($parts);
            }
        }

        foreach ($phrase_parts as $part) {
            // If quoted string:
            if (strpos($part, '"') === 0) {
                if (!$this->_validateQuotedString($part)) {
                    return false;
                }
                continue;
            }

            // Otherwise it's an atom:
            if (!$this->_validateAtom($part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Function to validate an atom which from rfc822 is:
     * atom = 1*<any CHAR except specials, SPACE and CTLs>
     *
     * If validation ($this->validate) has been turned off, then
     * validateAtom() doesn't actually check anything. This is so that you
     * can split a list of addresses up before encoding personal names
     * (umlauts, etc.), for example.
     *
     * @param string $atom The string to check.
     * @return bool Success or failure.
     */
    protected function _validateAtom(string $atom): bool
    {
        if (!$this->validate) {
            // Validation has been turned off; assume the atom is okay.
            return true;
        }

        // Check for any char from ASCII 0 - ASCII 127
        // mjansen patch 16 Sep 2015 start
        // Check for specials:
        if (preg_match('/[][()<>@,;\\:". ]/', $atom)) {
            return false;
        }

        // Check for control characters (ASCII 0-31):
        if (preg_match('/[\\x00-\\x1F]+/', $atom)) {
            return false;
        }
        #16291
        #17618
        if (!(bool) preg_match('//u', $atom)) {
            return false;
        }
        // mjansen patch 16 Sep 2015 end

        return true;
    }

    /**
     * Function to validate quoted string, which is:
     * quoted-string = <"> *(qtext/quoted-pair) <">
     *
     * @param string $qstring The string to check
     * @return bool Success or failure.
     */
    protected function _validateQuotedString(string $qstring): bool
    {
        // Leading and trailing "
        $qstring = substr($qstring, 1, -1);

        // Perform check, removing quoted characters first.
        return !preg_match('/[\x0D\\\\"]/', preg_replace('/\\\\./', '', $qstring));
    }

    /**
     * Function to validate a mailbox, which is:
     * mailbox =   addr-spec         ; simple address
     *           / phrase route-addr ; name and route-addr
     *
     * @param string &$mailbox The string to check.
     * @return bool Success or failure.
     */
    public function validateMailbox(string &$mailbox): bool
    {
        // A couple of defaults.
        $phrase = '';
        $comment = '';
        $comments = [];

        // Catch any RFC822 comments and store them separately.
        $_mailbox = $mailbox;
        while (trim($_mailbox) !== '') {
            $parts = explode('(', $_mailbox);
            $before_comment = $this->_splitCheck($parts, '(');
            if ($before_comment !== $_mailbox) {
                // First char should be a (.
                $comment = substr(str_replace($before_comment, '', $_mailbox), 1);
                $parts = explode(')', $comment);
                $comment = $this->_splitCheck($parts, ')');
                $comments[] = $comment;

                // +2 is for the brackets
                $_mailbox = substr($_mailbox, strpos($_mailbox, '(' . $comment) + strlen($comment) + 2);
            } else {
                break;
            }
        }

        foreach ($comments as $comment) {
            $mailbox = str_replace("($comment)", '', $mailbox);
        }

        $mailbox = trim($mailbox);

        // Check for name + route-addr
        if (substr($mailbox, -1) === '>' && $mailbox[0] !== '<') {
            $parts = explode('<', $mailbox);
            $name = $this->_splitCheck($parts, '<');

            $phrase = trim($name);
            $route_addr = trim(substr($mailbox, strlen($name . '<'), -1));

            if ($this->_validatePhrase($phrase) === false ||
                ($route_addr = $this->_validateRouteAddr($route_addr)) === false) {
                return false;
            }

            // Only got addr-spec
        } else {
            // First snip angle brackets if present.
            if ($mailbox[0] === '<' && substr($mailbox, -1) === '>') {
                $addr_spec = substr($mailbox, 1, -1);
            } else {
                $addr_spec = $mailbox;
            }

            if (($addr_spec = $this->_validateAddrSpec($addr_spec)) === false) {
                return false;
            }
        }

        // Construct the object that will be returned.
        $mbox = new stdClass();

        // Add the phrase (even if empty) and comments
        $mbox->personal = $phrase;
        $mbox->comment = $comments ?? [];

        if (isset($route_addr)) {
            $mbox->mailbox = $route_addr['local_part'];
            $mbox->host = $route_addr['domain'];
            if ($route_addr['adl'] !== '') {
                $mbox->adl = $route_addr['adl'];
            }
        } else {
            $mbox->mailbox = $addr_spec['local_part'];
            $mbox->host = $addr_spec['domain'];
        }

        $mailbox = $mbox;
        return true;
    }

    /**
     * This function validates a route-addr which is:
     * route-addr = "<" [route] addr-spec ">"
     *
     * Angle brackets have already been removed at the point of
     * getting to this function.
     *
     * @param string $route_addr The string to check.
     * @return mixed False on failure, or an array containing validated address/route information on success.
     */
    protected function _validateRouteAddr(string $route_addr)
    {
        // Check for colon.
        if (strpos($route_addr, ':') !== false) {
            $parts = explode(':', $route_addr);
            $route = $this->_splitCheck($parts, ':');
        } else {
            $route = $route_addr;
        }

        // If $route is same as $route_addr then the colon was in
        // quotes or brackets or, of course, non existent.
        if ($route === $route_addr) {
            unset($route);
            $addr_spec = $route_addr;
            if (($addr_spec = $this->_validateAddrSpec($addr_spec)) === false) {
                return false;
            }
        } else {
            // Validate route part.
            if (($route = $this->_validateRoute($route)) === false) {
                return false;
            }

            $addr_spec = substr($route_addr, strlen($route . ':'));

            // Validate addr-spec part.
            if (($addr_spec = $this->_validateAddrSpec($addr_spec)) === false) {
                return false;
            }
        }

        if (isset($route)) {
            $return['adl'] = $route;
        } else {
            $return['adl'] = '';
        }

        $return = array_merge($return, $addr_spec);
        return $return;
    }

    /**
     * Function to validate a route, which is:
     * route = 1#("@" domain) ":"
     *
     * @param string $route The string to check.
     * @return mixed False on failure, or the validated $route on success.
     */
    protected function _validateRoute(string $route)
    {
        // Split on comma.
        $domains = explode(',', trim($route));

        foreach ($domains as $domain) {
            $domain = str_replace('@', '', trim($domain));
            if (!$this->_validateDomain($domain)) {
                return false;
            }
        }

        return $route;
    }

    /**
     * Function to validate a domain, though this is not quite what
     * you expect of a strict internet domain.
     *
     * domain = sub-domain *("." sub-domain)
     *
     * @param string $domain The string to check.
     * @return mixed False on failure, or the validated domain on success.
     */
    protected function _validateDomain(string $domain)
    {
        // Note the different use of $subdomains and $sub_domains
        $subdomains = explode('.', $domain);

        $sub_domains = [];
        while (count($subdomains) > 0) {
            $sub_domains[] = $this->_splitCheck($subdomains, '.');
            for ($i = 0; $i < $this->index + 1; $i++) {
                array_shift($subdomains);
            }
        }

        foreach ($sub_domains as $sub_domain) {
            if (!$this->_validateSubdomain(trim($sub_domain))) {
                return false;
            }
        }

        // Managed to get here, so return input.
        return $domain;
    }

    /**
     * Function to validate a subdomain:
     *   subdomain = domain-ref / domain-literal
     *
     * @param string $subdomain The string to check.
     * @return bool Success or failure.
     */
    protected function _validateSubdomain(string $subdomain): bool
    {
        if (preg_match('|^\[(.*)]$|', $subdomain, $arr)) {
            if (!$this->_validateDliteral($arr[1])) {
                return false;
            }
        } elseif (!$this->_validateAtom($subdomain)) {
            return false;
        }

        // Got here, so return successful.
        return true;
    }

    /**
     * Function to validate a domain literal:
     *   domain-literal =  "[" *(dtext / quoted-pair) "]"
     *
     * @param string $dliteral The string to check.
     * @return bool Success or failure.
     */
    protected function _validateDliteral(string $dliteral): bool
    {
        return !preg_match('/(.)[][\x0D\\\\]/', $dliteral, $matches) &&
            ((!isset($matches[1])) || $matches[1] != '\\');
    }

    /**
     * Function to validate an addr-spec.
     *
     * addr-spec = local-part "@" domain
     *
     * @param string $addr_spec The string to check.
     * @return mixed False on failure, or the validated addr-spec on success.
     */
    protected function _validateAddrSpec(string $addr_spec)
    {
        $addr_spec = trim($addr_spec);

        // mjansen patch 16 Sep 2016 start
        $validateState = $this->validate;
        // mjansen patch 16 Sep 2016 end
        // Split on @ sign if there is one.
        if (strpos($addr_spec, '@') !== false) {
            $parts = explode('@', $addr_spec);
            $local_part = $this->_splitCheck($parts, '@');
            $domain = substr($addr_spec, strlen($local_part . '@'));
            // mjansen patch 16 Sep 2016 start
            if (substr_count($addr_spec, '@') !== 1 && $local_part === '') {
                $this->validate = false;
                $local_part = $addr_spec;
                $domain = $this->default_domain;
            }
            // mjansen patch 16 Sep 2016 end
            // No @ sign so assume the default domain.
        } else {
            $local_part = $addr_spec;
            $domain = $this->default_domain;
        }

        if (($local_part = $this->_validateLocalPart($local_part)) === false) {
            return false;
        }
        // mjansen patch 16 Sep 2016 start
        if ($validateState !== $this->validate) {
            $this->validate = $validateState;
        }
        // mjansen patch 16 Sep 2016 end
        if (($domain = $this->_validateDomain($domain)) === false) {
            return false;
        }

        // Got here so return successful.
        return ['local_part' => $local_part, 'domain' => $domain];
    }

    /**
     * Function to validate the local part of an address:
     *   local-part = word *("." word)
     *
     * @access private
     * @param string $local_part
     * @return mixed False on failure, or the validated local part on success.
     */
    protected function _validateLocalPart(string $local_part)
    {
        $parts = explode('.', $local_part);
        $words = [];

        // Split the local_part into words.
        while (count($parts) > 0) {
            $words[] = $this->_splitCheck($parts, '.');
            for ($i = 0; $i < $this->index + 1; $i++) {
                array_shift($parts);
            }
        }

        // Validate each word.
        foreach ($words as $word) {
            // iszmais patch 19 May 2020 start
            // word cannot be empty (#17317)
            //if ($word === '') {
            //    return false;
            //}
            // iszmais patch 19 May 2020 end
            // If this word contains an unquoted space, it is invalid. (6.2.4)
            if (strpos($word, ' ') && $word[0] !== '"') {
                // mjansen patch 24 Feb 2016 start
                // Mantis issue #18018
                // # http://haacked.com/archive/2007/08/21/i-knew-how-to-validate-an-email-address-until-i.aspx/
                //return false;
                // mjansen patch 24 Feb 2016 end
            }

            if ($this->_validatePhrase(trim($word)) === false) {
                return false;
            }
        }

        // Managed to get here, so return the input.
        return $local_part;
    }

    /**
     * Returns an approximate count of how many addresses are in the
     * given string. This is APPROXIMATE as it only splits based on a
     * comma which has no preceding backslash. Could be useful as
     * large amounts of addresses will end up producing *large*
     * structures when used with parseAddressList().
     *
     * @param  string $data Addresses to count
     * @return int          Approximate count
     */
    public function approximateCount(string $data): int
    {
        return count(preg_split('/(?<!\\\\),/', $data));
    }

    /**
     * This is a email validating function separate to the rest of the
     * class. It simply validates whether an email is of the common
     * internet form: <user>@<domain>. This can be sufficient for most
     * people. Optional stricter mode can be utilised which restricts
     * mailbox characters allowed to alphanumeric, full stop, hyphen
     * and underscore.
     *
     * @param  string $data   Address to check
     * @param  bool   $strict Optional stricter mode
     * @return mixed          False if it fails, an indexed array
     *                        username/domain if it matches
     */
    public function isValidInetAddress(string $data, bool $strict = false)
    {
        $regex =
            $strict ?
                '/^([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i' :
                '/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$/i';
        if (preg_match($regex, trim($data), $matches)) {
            return [$matches[1], $matches[2]];
        }

        return false;
    }
}
