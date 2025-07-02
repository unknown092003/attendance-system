// JavaScript for the home page
document.addEventListener("DOMContentLoaded", function () {
  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.opacity = "0";
      setTimeout(function () {
        alert.style.display = "none";
      }, 500);
    }, 5000);
  });

  // Stopwatch elements and buttons
  const stopwatchDisplay = document.getElementById("stopwatch");
  const stopwatchStatus = document.getElementById("stopwatch-status");
  const timeInBtn = document.getElementById("time-in-btn");
  const timeOutBtn = document.getElementById("time-out-btn");
  const endDayBtn = document.getElementById("end-day-btn");
  const userId = document.getElementById("user-id")?.value;

  // Variables for tracking time
  let stopwatchInterval;
  let isRunning = false;
  let totalElapsedSeconds = 0;

  // Format seconds as HH:MM:SS
  function formatTime(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return [
      hours.toString().padStart(2, "0"),
      minutes.toString().padStart(2, "0"),
      seconds.toString().padStart(2, "0"),
    ].join(":");
  }

  // Update stopwatch display
  function updateStopwatchDisplay() {
    if (stopwatchDisplay) {
      stopwatchDisplay.textContent = formatTime(totalElapsedSeconds);
    }
  }

  // Update buttons based on stopwatch state
  function updateButtonStates(state) {
    // Check for journal lock first
    const hasJournal = document.getElementById("has-journal").value === "true";
    if (hasJournal) {
      if (timeInBtn) timeInBtn.disabled = true;
      if (timeOutBtn) timeOutBtn.disabled = true;
      if (endDayBtn) endDayBtn.disabled = true;
      return;
    }

    if (state === "active") {
      if (timeInBtn) timeInBtn.disabled = true;
      if (timeOutBtn) timeOutBtn.disabled = false;
      if (endDayBtn) endDayBtn.disabled = true;
    } else if (state === "paused") {
      if (timeInBtn) timeInBtn.disabled = false;
      if (timeOutBtn) timeOutBtn.disabled = true;
      if (endDayBtn) endDayBtn.disabled = false;
    } else if (state === "reset") {
      if (timeInBtn) timeInBtn.disabled = false;
      if (timeOutBtn) timeOutBtn.disabled = true;
      if (endDayBtn) endDayBtn.disabled = false;
    }
  }

  // Fetch attendance data from server and update UI
  function fetchAttendanceData() {
    fetch("/attendance-system/active-attendance", {
      method: "GET",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Clear any existing interval
          if (stopwatchInterval) {
            clearInterval(stopwatchInterval);
          }

          // Update based on attendance status
          if (data.active) {
            // Active session - start counting from current accumulated time
            isRunning = true;
            totalElapsedSeconds = data.total_seconds || 0;
            updateStopwatchDisplay();

            // Start incrementing from server time
            stopwatchInterval = setInterval(() => {
              totalElapsedSeconds++;
              updateStopwatchDisplay();
            }, 1000);

            if (stopwatchStatus) {
              stopwatchStatus.textContent = "Active";
              stopwatchStatus.classList.add("active");
            }
            updateButtonStates("active");
          } else if (data.status === "paused" && data.total_seconds > 0) {
            // Paused session - show accumulated time without counting
            isRunning = false;
            totalElapsedSeconds = data.total_seconds;
            updateStopwatchDisplay();

            if (stopwatchStatus) {
              stopwatchStatus.textContent = "Paused";
              stopwatchStatus.classList.remove("active");
            }
            updateButtonStates("paused");
          } else {
            // No active session or day ended
            isRunning = false;
            totalElapsedSeconds = 0;
            updateStopwatchDisplay();

            if (stopwatchStatus) {
              stopwatchStatus.textContent = "Not active";
              stopwatchStatus.classList.remove("active");
            }
            updateButtonStates("reset");
          }
        }
      })
      .catch((error) =>
        console.error("Error fetching attendance data:", error)
      );
  }

  // Initialize by fetching attendance data
  fetchAttendanceData();

  // Periodically sync with server (every 30 seconds)
  setInterval(fetchAttendanceData, 30000);

  // Time In button event
  if (timeInBtn) {
    timeInBtn.addEventListener("click", () => {
      fetch("/attendance-system/time-in", {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Refresh attendance data from server
            fetchAttendanceData();

            // Show success alert
            const alert = document.createElement("div");
            alert.className = "alert success";
            alert.textContent = "Time-in recorded successfully";
            document
              .querySelector("main")
              .insertBefore(alert, document.querySelector("main").firstChild);

            setTimeout(() => {
              alert.style.opacity = "0";
              setTimeout(() => alert.remove(), 500);
            }, 5000);
          } else {
            alert("Failed to record time-in. Please try again.");
          }
        })
        .catch(() => alert("Error recording time-in. Please try again."));
    });
  }

  // Time Out button event
  if (timeOutBtn) {
    timeOutBtn.addEventListener("click", () => {
      fetch("/attendance-system/time-out", {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Refresh attendance data from server
            fetchAttendanceData();

            // Show success alert
            const alert = document.createElement("div");
            alert.className = "alert success";
            alert.textContent = "Time-out recorded successfully";
            document
              .querySelector("main")
              .insertBefore(alert, document.querySelector("main").firstChild);

            setTimeout(() => {
              alert.style.opacity = "0";
              setTimeout(() => alert.remove(), 500);
            }, 5000);
          } else {
            alert("Failed to record time-out. Please try again.");
          }
        })
        .catch(() => alert("Error recording time-out. Please try again."));
    });
  }

  // End Day button event
  if (endDayBtn) {
    endDayBtn.addEventListener("click", () => {
      const hasJournalElement = document.getElementById("has-journal");
      const hasJournal =
        hasJournalElement && hasJournalElement.value === "true";

      if (hasJournal) {
        if (
          confirm(
            "You already have a journal entry for today. Do you want to end your day and reset the timer?"
          )
        ) {
          // End day directly if journal already exists
          fetch("/attendance-system/end-day", {
            method: "POST",
            headers: {
              "X-Requested-With": "XMLHttpRequest",
              "Content-Type": "application/json",
            },
            body: JSON.stringify({ journal: "Journal already submitted" }),
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                // Refresh the page to show updated state
                window.location.href = "/attendance-system/home";
              } else {
                alert("Failed to end day. Please try again.");
              }
            })
            .catch((error) => {
              console.error("Error ending day:", error);
              alert("An error occurred. Please try again.");
            });
        }
      } else {
        const modal = document.getElementById("journal-modal");
        if (modal) {
          modal.style.display = "block";
        }
      }
    });
  }

  // Journal modal handlers
  const modal = document.getElementById("journal-modal");
  const closeBtn = document.querySelector(".close");
  const cancelBtn = document.getElementById("cancel-journal");
  const submitBtn = document.getElementById("submit-journal");
  const journalText = document.getElementById("journal-text");
  let isEditMode = false;

  // Edit journal button handler
  const editJournalBtn = document.getElementById("edit-journal-btn");
  if (editJournalBtn) {
    editJournalBtn.addEventListener("click", () => {
      isEditMode = true;
      document.getElementById("journal-modal-title").textContent =
        "Edit Today's Journal";
      document.getElementById("journal-modal-description").textContent =
        "Update your journal entry for today:";
      document.getElementById("submit-journal").textContent = "Update Journal";
      journalText.value = document
        .querySelector(".journal-content")
        .textContent.replace(/<br\s*\/?>/gi, "\n")
        .trim();
      if (modal) modal.style.display = "block";
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      if (modal) modal.style.display = "none";
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      if (modal) modal.style.display = "none";
    });
  }

  if (modal) {
    window.addEventListener("click", (event) => {
      if (event.target === modal) modal.style.display = "none";
    });
  }

  if (submitBtn && journalText) {
    submitBtn.addEventListener("click", () => {
      const journal = journalText.value.trim();

      if (!journal) {
        alert("Please write a journal entry before ending your day.");
        return;
      }

      // Submit the end-day request directly
      console.log('Submitting end day with journal:', journal);
      fetch("/attendance-system/end-day", {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ journal }),
      })
        .then((response) => {
          if (!response.ok) {
            return response.text().then((text) => {
              throw new Error(`Server error: ${text}`);
            });
          }
          const contentType = response.headers.get("content-type");
          if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Invalid server response format");
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            if (modal) modal.style.display = "none";

            const alert = document.createElement("div");
            alert.className = "alert success";
            alert.textContent = "Day ended successfully. Journal saved.";
            document
              .querySelector("main")
              .insertBefore(alert, document.querySelector("main").firstChild);

            // Refresh the page to show updated state
            setTimeout(() => {
              window.location.href = "/attendance-system/home";
            }, 1500);
          } else {
            alert("Failed to end day. Please try again.");
          }
        })
        .catch((error) => {
          console.error("Error ending day:", error);
          alert("An error occurred. Please try again.");
        });
    });
  }
});
