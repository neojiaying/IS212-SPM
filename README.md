# IS212-SPM
Building a better BOSS bidding system for SMU

Functional Requirements:
The students in Merlion University use BIOS (BIdding Online System) to enroll for their courses. They select the courses they wish to enroll and bid for it using virtual dollars (e$). A course can have multiple sections and a section is taught by an instructor. Also, An instructor can teach one or more sections.

BIOS are used by students as well as administrators. For students the following functions are provided:

1. Login
A student will log in with his/her email ID and password.
Upon success, the student should be able to see the balance e$ along with a welcome message.
Upon failure, the app outputs a proper error message and requests the user to login again.

2. Bid for a section
- A Student can place a bid by entering a course code, section number, and e$ amount for bidding.
- The bidding will be possible only during active bidding rounds.
- For the bidding round 1, the student can bid for the course that are offered by his/her own school.
- For the bidding round 2, the student can bid for any courses.
- The bid will be successful when all the following criteria is satisfied.
- A Student can bid for any section as long as they have enough e$, the class and exam timetables do not clash, and s/he has fulfilled the necessary pre-requisite courses.
- A student can bid at most for 5 sections.
- A student can only bid for one section per course.
- When the bid is successful, the app outputs a proper success message along with the balance e$.
Otherwise, the app shows a proper error message. You can also design your application UI not to even allow impossible bids.

3. Drop a bid
- A Student can drop a bid by specifying a course id and section number.
- When the input is valid, the app will cancel the bid and return back the full e$ credit. The updated e$ balance should be shown to the user.
- Dropping a bid can be done only during active bidding rounds.

4. Drop a section
- After a successful bid, a student can drop a section by specifying the course id and section number.
- When drop is successful, and the student will get back the full e$ credit, and the app will show the e$ balance.
- Once you drop a successful bid, you will have to rebid for the section.
- Dropping of section is done when a round is active. (in other words, success bids from round 2 are final and cannot be dropped).

5. View bidding results
- Show a table to show the bidding status -- the table should include the course id, section number, bid amount, status of bidding. The status will be one of the following: Pending, Success, Fail.

For administrator, the following functions are provided:

1. Login
- An administrator will log in with the username "admin" and password. The admin will login from the same login page as student users.

2. Bootstrap
- The administrator can bootstrap the BIOS system with the given data.
- The bidding round 1 will start automatically upon the completion of the bootstrap.

3. Starting and clearing rounds
- The bidding round will be manually started and cleared by the administrator (except that the round 1 starts automatically after bootstrap).
- Round 1
--> When the round 1 is closed, students will be able to see the bidding results.
--> The first round of bidding is for students to bid for courses offered by their school.

- Round 2
--> The second round of bidding is for students to bid for courses offered by their school and courses offered by other schools.
